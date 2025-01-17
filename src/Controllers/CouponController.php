<?php

namespace Controllers;

use Services\CouponService;
use Models\AirtableRepository;
use Utils\Logger;
use GuzzleHttp\Client;
use Services\EmailService;
use Services\FileUploadService;

class CouponController
{
    private CouponService $couponService;
    private AirtableRepository $airtableRepository;
    private Logger $logger;
    private EmailService $emailService;
    private FileUploadService $fileUploadService;

    public function __construct()
    {
        $this->couponService = new CouponService();
        $this->airtableRepository = new AirtableRepository(new Client());
        $this->logger = new Logger();
        $this->emailService = new EmailService();
        $this->fileUploadService = new FileUploadService();
    }

    public function validateCoupon(): void
    {
        // Vérifier que c'est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Méthode non autorisée'], 405);
            return;
        }

        // Récupérer et décoder le JSON du body
        $rawInput = file_get_contents('php://input');
        $this->logger->debug("Données brutes reçues", ['raw' => $rawInput]);
        $jsonData = json_decode($rawInput, true);
        $this->logger->debug("Données JSON décodées", ['data' => $jsonData]);

        // Vérifier que le code est présent
        if (!isset($jsonData['code']) || empty($jsonData['code'])) {
            $this->jsonResponse(['error' => 'Code coupon manquant'], 400);
            return;
        }

        $code = trim($jsonData['code']);

        try {
            $this->logger->info("Tentative de validation du coupon", ['code' => $code]);

            // Valider le coupon via le service
            $validationResult = $this->couponService->validateCoupon($code);

            if (!$validationResult['isValid']) {
                $this->jsonResponse([
                    'valid' => false,
                    'error' => $validationResult['error']
                ], 400);
                return;
            }

            $this->jsonResponse([
                'valid' => true,
                'motif' => $validationResult['motif']
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Erreur de validation", [
                'code' => $code,
                'error' => $e->getMessage()
            ]);
            $this->jsonResponse([
                'valid' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\RuntimeException $e) {
            $this->logger->error("Erreur technique lors de la validation", [
                'code' => $code,
                'error' => $e->getMessage()
            ]);
            $this->jsonResponse([
                'valid' => false,
                'error' => 'Une erreur est survenue lors de la validation'
            ], 500);
        }
    }

    public function activateCoupon(): void
    {
        // Vérifier que c'est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Méthode non autorisée'], 405);
            return;
        }

        // Récupérer les données POST et les fichiers
        $jsonData = $_POST;
        $files = $_FILES;

        // Valider les données requises
        $requiredFields = ['code', 'nom', 'telephone', 'email', 'entreprise'];
        foreach ($requiredFields as $field) {
            if (!isset($jsonData[$field]) || empty(trim($jsonData[$field]))) {
                $this->jsonResponse(['error' => "Le champ '$field' est requis"], 400);
                return;
            }
        }

        // Nettoyer les données
        $data = array_map('trim', [
            'code' => $jsonData['code'],
            'nom' => $jsonData['nom'],
            'telephone' => $jsonData['telephone'],
            'email' => $jsonData['email'],
            'entreprise' => $jsonData['entreprise']
        ]);

        try {
            $this->logger->info("Tentative d'activation du coupon", ['code' => $data['code']]);

            // Valider le coupon via le service
            $validationResult = $this->couponService->validateCoupon($data['code']);
            if (!$validationResult['isValid']) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $validationResult['error']
                ], 400);
                return;
            }

            // Récupérer le coupon validé
            $coupon = $this->airtableRepository->findCouponByLastFiveChars($data['code']);

            // Valider le format des données
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Format d\'email invalide'
                ], 400);
                return;
            }

            if (!preg_match('/^(?:\+|00)\d{1,4}\s*\d{6,14}$|^0\s*\d{8,9}$/', $data['telephone'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Format de téléphone invalide'
                ], 400);
                return;
            }

            // Gérer l'upload des fichiers
            $uploadResults = [];

            if (isset($files['carteVisiteRecto']) && !empty($files['carteVisiteRecto']['tmp_name'])) {
                $rectoResult = $this->fileUploadService->handleUpload($files['carteVisiteRecto'], 'recto_' . $data['code']);
                if (!$rectoResult['success']) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Erreur lors de l\'upload du recto : ' . $rectoResult['error']
                    ], 400);
                    return;
                }
                $uploadResults['recto'] = $rectoResult['path'];
            }

            if (isset($files['carteVisiteVerso']) && !empty($files['carteVisiteVerso']['tmp_name'])) {
                $versoResult = $this->fileUploadService->handleUpload($files['carteVisiteVerso'], 'verso_' . $data['code']);
                if (!$versoResult['success']) {
                    // Si le recto a été uploadé, le supprimer
                    if (isset($uploadResults['recto'])) {
                        $this->fileUploadService->deleteFile($uploadResults['recto']);
                    }
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Erreur lors de l\'upload du verso : ' . $versoResult['error']
                    ], 400);
                    return;
                }
                $uploadResults['verso'] = $versoResult['path'];
            }

            // Mettre à jour le coupon dans Airtable
            $updateData = [
                'nom' => $data['nom'],
                'telephone' => $data['telephone'],
                'email' => $data['email'],
                'entreprise' => $data['entreprise'],
                'dateActivation' => date('Y-m-d')
            ];

            // Ajouter les chemins des fichiers s'ils existent
            if (!empty($uploadResults['recto'])) {
                $updateData['carteVisiteRecto'] = $uploadResults['recto'];
            }
            if (!empty($uploadResults['verso'])) {
                $updateData['carteVisiteVerso'] = $uploadResults['verso'];
            }

            $this->airtableRepository->updateCouponActivation($coupon['id'], $updateData);

            $this->logger->info("Activation du coupon réussie", [
                'code' => $data['code'],
                'email' => $data['email']
            ]);

            // Send confirmation email
            $emailSent = $this->emailService->sendActivationConfirmation(
                $data['email'],
                $data['nom'],
                $coupon['fields']['motif'] ?? 'votre commande'
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Coupon activé avec succès',
                'emailSent' => $emailSent,
                'motif' => $coupon['fields']['motif'] ?? 'votre commande'
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning("Erreur de validation lors de l'activation", [
                'code' => $data['code'],
                'error' => $e->getMessage()
            ]);
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\RuntimeException $e) {
            $this->logger->error("Erreur technique lors de l'activation", [
                'code' => $data['code'],
                'error' => $e->getMessage()
            ]);
            $this->jsonResponse([
                'success' => false,
                'error' => 'Une erreur est survenue lors de l\'activation'
            ], 500);
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
