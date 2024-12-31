<?php

namespace Services;

use Models\AirtableRepository;
use Utils\Logger;

class CouponService
{
    private Logger $logger;
    private AirtableRepository $airtableRepository;

    public function __construct(AirtableRepository $airtableRepository = null)
    {
        $this->logger = new Logger();
        $this->airtableRepository = $airtableRepository ?? new AirtableRepository(new \GuzzleHttp\Client());
    }

    /**
     * Valide le format des 5 derniers caractères d'un code coupon
     * @param string $shortCode Les 5 derniers caractères du code coupon
     * @return bool True si le format est valide
     * @throws \InvalidArgumentException Si le format est invalide
     */
    public function validateCouponFormat(string $shortCode): bool
    {
        // Vérifier la longueur (5 caractères)
        if (strlen($shortCode) !== 5) {
            throw new \InvalidArgumentException('Le code coupon doit faire exactement 5 caractères');
        }

        // Vérifier que seuls les caractères alphanumériques sont présents
        if (!ctype_alnum($shortCode)) {
            throw new \InvalidArgumentException('Le code coupon ne doit contenir que des caractères alphanumériques');
        }

        return true;
    }

    /**
     * Vérifie si un coupon existe et est disponible dans Airtable
     * @param string $shortCode Les 5 derniers caractères du code coupon
     * @return array|null Les données du coupon ou null si non trouvé
     */
    private function findAndValidateCoupon(string $shortCode): ?array
    {
        $this->logger->debug("Recherche du coupon dans Airtable", ['shortCode' => $shortCode]);

        $coupon = $this->airtableRepository->findCouponByLastFiveChars($shortCode);

        if (!$coupon) {
            $this->logger->debug("Coupon non trouvé dans Airtable", ['shortCode' => $shortCode]);
            return null;
        }

        // Vérifier si le coupon a déjà été utilisé
        if (!empty($coupon['fields']['email'])) {
            $this->logger->debug("Coupon déjà utilisé", [
                'shortCode' => $shortCode,
                'email' => $coupon['fields']['email']
            ]);
            return null;
        }

        return $coupon;
    }

    /**
     * Valide complètement un coupon en utilisant les 5 derniers caractères
     * @param string $shortCode Les 5 derniers caractères du code coupon
     * @return array Résultat de la validation avec statut et détails
     */
    public function validateCoupon(string $shortCode): array
    {
        try {
            $this->logger->info("Tentative de validation du coupon", ['code' => $shortCode]);

            // Validation du format
            $this->validateCouponFormat($shortCode);

            // Recherche et validation dans Airtable
            $coupon = $this->findAndValidateCoupon($shortCode);

            if (!$coupon) {
                $error = "Code coupon invalide ou inexistant";
                $this->logger->warning("Format de coupon invalide", [
                    'code' => $shortCode,
                    'error' => $error
                ]);
                return [
                    'isValid' => false,
                    'error' => $error
                ];
            }

            $this->logger->info("Coupon validé avec succès", [
                'code' => $shortCode,
                'motif' => $coupon['fields']['motif'] ?? 'Non spécifié'
            ]);

            return [
                'isValid' => true,
                'motif' => $coupon['fields']['motif'] ?? 'Non spécifié'
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'isValid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
