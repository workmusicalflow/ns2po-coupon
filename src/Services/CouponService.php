<?php

namespace Services;

class CouponService
{
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
     * Vérifie si un coupon existe dans Airtable en utilisant les 5 derniers caractères
     * @param string $shortCode Les 5 derniers caractères du code coupon
     * @return bool True si le coupon existe
     */
    public function checkCouponExists(string $shortCode): bool
    {
        // Dans la vraie implémentation, on cherchera dans Airtable tous les codes qui se terminent par ces 5 caractères
        // SELECT * FROM Coupons WHERE SUBSTR(recordId, -5) = $shortCode
        return $shortCode === 'HTvLB';
    }

    /**
     * Vérifie si un coupon est disponible en utilisant les 5 derniers caractères
     * @param string $shortCode Les 5 derniers caractères du code coupon
     * @return bool True si le coupon est disponible
     */
    public function isCouponAvailable(string $shortCode): bool
    {
        // Dans la vraie implémentation, on vérifiera dans Airtable si le coupon correspondant est disponible
        // SELECT * FROM Coupons WHERE SUBSTR(recordId, -5) = $shortCode AND email IS NULL
        return $shortCode === 'HTvLB';
    }

    /**
     * Valide complètement un coupon en utilisant les 5 derniers caractères
     * @param string $shortCode Les 5 derniers caractères du code coupon
     * @return array Résultat de la validation avec statut et détails
     */
    public function validateCoupon(string $shortCode): array
    {
        try {
            // Validation du format
            $this->validateCouponFormat($shortCode);

            // Vérification de l'existence
            if (!$this->checkCouponExists($shortCode)) {
                return [
                    'isValid' => false,
                    'error' => 'Code coupon invalide ou inexistant'
                ];
            }

            // Vérification de la disponibilité
            if (!$this->isCouponAvailable($shortCode)) {
                return [
                    'isValid' => false,
                    'error' => 'Ce code coupon a déjà été utilisé'
                ];
            }

            // Pour le test, simulation d'un coupon valide
            // Dans la vraie implémentation, on récupérera le motif depuis Airtable
            return [
                'isValid' => true,
                'motif' => '100 cartes de visite'
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'isValid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}