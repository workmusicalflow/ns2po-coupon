<?php

namespace Services;

use Utils\Logger;

class FileUploadService
{
    private $uploadDir;
    private $logger;
    private const MAX_FILE_SIZE = 10485760; // 10 Mo en octets
    private const ALLOWED_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'application/pdf'
    ];

    public function __construct()
    {
        $this->uploadDir = dirname(__DIR__) . '/uploads/cartes-visite/';
        $this->logger = new Logger();

        // Créer le répertoire d'upload s'il n'existe pas
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Valide et enregistre un fichier uploadé
     * 
     * @param array $file Fichier uploadé ($_FILES['nom_du_champ'])
     * @param string $prefix Préfixe pour le nom du fichier
     * @return array{success: bool, path?: string, error?: string}
     */
    public function handleUpload($file, $prefix = '')
    {
        try {
            // Vérifications de base
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                return ['success' => false, 'error' => 'Fichier non reçu'];
            }

            // Vérification de la taille
            if ($file['size'] > self::MAX_FILE_SIZE) {
                return ['success' => false, 'error' => 'Le fichier dépasse la taille maximale autorisée (10 Mo)'];
            }

            // Vérification du type MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, self::ALLOWED_TYPES)) {
                return ['success' => false, 'error' => 'Type de fichier non autorisé'];
            }

            // Génération d'un nom de fichier unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = $prefix . '_' . uniqid() . '.' . $extension;
            $targetPath = $this->uploadDir . $fileName;

            // Déplacement du fichier
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new \Exception('Erreur lors du déplacement du fichier');
            }

            // Journalisation du succès
            $this->logger->info('Fichier uploadé avec succès', [
                'nom' => $fileName,
                'type' => $mimeType,
                'taille' => $file['size']
            ]);

            // Construire l'URL complète
            $baseUrl = rtrim($_ENV['APP_URL'] ?? 'https://topdigitalevel.site', '/');
            $fullUrl = $baseUrl . '/ns2po-coupon/src/uploads/cartes-visite/' . $fileName;

            return [
                'success' => true,
                'path' => $fullUrl
            ];
        } catch (\Exception $e) {
            // Journalisation de l'erreur
            $this->logger->error('Erreur lors de l\'upload du fichier', [
                'error' => $e->getMessage(),
                'file' => $file['name'] ?? 'unknown'
            ]);

            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors de l\'upload du fichier'
            ];
        }
    }

    /**
     * Supprime un fichier uploadé
     * 
     * @param string $path Chemin du fichier à supprimer
     * @return bool
     */
    public function deleteFile($path)
    {
        try {
            // Extraire le nom du fichier de l'URL complète
            $fileName = basename($path);
            $fullPath = $this->uploadDir . $fileName;
            if (file_exists($fullPath)) {
                unlink($fullPath);
                $this->logger->info('Fichier supprimé avec succès', ['path' => $path]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression du fichier', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            return false;
        }
    }
}
