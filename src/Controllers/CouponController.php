<?php

namespace Controllers;

use Services\CouponService;
use Models\AirtableRepository;
use Utils\Logger;
use GuzzleHttp\Client;

class CouponController
{
    private CouponService $couponService;
    private AirtableRepository $airtableRepository;
    private Logger $logger;

    public function __construct()
    {
        $this->couponService = new CouponService();
        $this->airtableRepository = new AirtableRepository(new Client());
        $this->logger = new Logger();
    }

    public function validateCoupon(): void
    {
        // Vérifier que c'est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Méthode non autorisée'], 405);
            return;
        }

        // Récupérer et décoder le JSON du body
        $jsonData = json_decode(file_get_contents('php://input'), true);

        // Vérifier que le code est présent
        if (!isset($jsonData['code']) || empty($jsonData['code'])) {
            $this->jsonResponse(['error' => 'Code coupon manquant'], 400);
            return;
        }

        $code = trim($jsonData['code']);

        try {
            $this->logger->info("Tentative de validation du coupon", ['code' => $code]);

            // Valider le format du code
            $validationResult = $this->couponService->validateCoupon($code);

            if (!$validationResult['isValid']) {
                $this->logger->warning("Format de coupon invalide", [
                    'code' => $code,
                    'error' => $validationResult['error']
                ]);
                $this->jsonResponse([
                    'valid' => false,
                    'error' => $validationResult['error']
                ], 400);
                return;
            }

            // Vérifier dans Airtable
            $coupon = $this->airtableRepository->findCouponByLastFiveChars($code);
            $this->logger->debug("Résultat de la recherche Airtable", ['found' => !empty($coupon)]);

            if (!$coupon) {
                $this->logger->warning("Coupon non trouvé", ['code' => $code]);
                $this->jsonResponse([
                    'valid' => false,
                    'error' => 'Code coupon invalide ou inexistant'
                ], 404);
                return;
            }

            // Vérifier si le coupon n'est pas déjà utilisé
            if (isset($coupon['fields']['email']) && !empty($coupon['fields']['email'])) {
                $this->logger->warning("Tentative d'utilisation d'un coupon déjà utilisé", [
                    'code' => $code,
                    'email' => $coupon['fields']['email']
                ]);
                $this->jsonResponse([
                    'valid' => false,
                    'error' => 'Ce code coupon a déjà été utilisé'
                ], 400);
                return;
            }

            $this->logger->info("Validation du coupon réussie", [
                'code' => $code,
                'motif' => $coupon['fields']['motif'] ?? 'Non spécifié'
            ]);

            // Coupon valide et disponible
            $this->jsonResponse([
                'valid' => true,
                'motif' => $coupon['fields']['motif'] ?? 'Non spécifié'
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

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}