<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CouponServiceTest extends TestCase
{
    private $couponService;

    protected function setUp(): void
    {
        $this->couponService = new \Services\CouponService();
    }

    #[Test]
    public function testCouponFormatValidation(): void
    {
        // Le format attendu est une chaîne de 5 caractères (les 5 derniers du code Airtable)
        $this->expectException(\InvalidArgumentException::class);
        $this->couponService->validateCouponFormat('123'); // Trop court

        $this->expectException(\InvalidArgumentException::class);
        $this->couponService->validateCouponFormat('123456'); // Trop long

        $this->expectException(\InvalidArgumentException::class);
        $this->couponService->validateCouponFormat('@HTvL'); // Caractères spéciaux

        // Format valide (5 derniers caractères du code complet)
        $this->assertTrue($this->couponService->validateCouponFormat('HTvLB'));
    }

    #[Test]
    public function testCouponExistence(): void
    {
        // Le coupon doit exister dans la base Airtable
        // Test avec les 5 derniers caractères uniquement
        $this->assertFalse($this->couponService->checkCouponExists('XXXXX'));
        $this->assertTrue($this->couponService->checkCouponExists('HTvLB'));
    }

    #[Test]
    public function testCouponAvailability(): void
    {
        // Le coupon ne doit pas déjà être utilisé (champ email vide)
        // Test avec les 5 derniers caractères uniquement
        $this->assertTrue($this->couponService->isCouponAvailable('HTvLB'));
        $this->assertFalse($this->couponService->isCouponAvailable('USED5'));
    }

    #[Test]
    public function testCompleteCouponValidation(): void
    {
        // Test du processus complet de validation
        $validationResult = $this->couponService->validateCoupon('HTvLB');
        $this->assertTrue($validationResult['isValid']);
        $this->assertEquals('100 cartes de visite', $validationResult['motif']);

        $invalidResult = $this->couponService->validateCoupon('INVALID123');
        $this->assertFalse($invalidResult['isValid']);
        $this->assertArrayHasKey('error', $invalidResult);
    }
}