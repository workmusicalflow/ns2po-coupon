<?php

namespace Tests\Unit\Models;

use Models\AirtableRepository;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class AirtableRepositoryTest extends TestCase
{
    private AirtableRepository $repository;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $this->repository = new AirtableRepository(
            $client,
            'test_api_key',
            'test_base_id',
            'test_table_name'
        );
    }

    public function testFindCouponByLastFiveCharsReturnsRecordWhenFound(): void
    {
        // Arrange
        $lastFiveChars = "HTvLB";
        $expectedRecord = [
            "id" => "recDa96prat6HTvLB",
            "fields" => [
                "motif" => "Anniversaire",
                "email" => null,
                "nom" => null,
                "telephone" => null,
                "entreprise" => null,
                "dateActivation" => null
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode([
                "records" => [$expectedRecord]
            ]))
        );

        // Act
        $result = $this->repository->findCouponByLastFiveChars($lastFiveChars);

        // Assert
        $this->assertEquals($expectedRecord, $result);
    }

    public function testFindCouponByLastFiveCharsReturnsNullWhenNotFound(): void
    {
        // Arrange
        $lastFiveChars = "XXXXX";
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                "records" => []
            ]))
        );

        // Act
        $result = $this->repository->findCouponByLastFiveChars($lastFiveChars);

        // Assert
        $this->assertNull($result);
    }

    public function testFindCouponByLastFiveCharsThrowsExceptionOnApiError(): void
    {
        // Arrange
        $lastFiveChars = "HTvLB";
        $this->mockHandler->append(
            new Response(401, [], json_encode([
                "error" => "UNAUTHORIZED"
            ]))
        );

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Erreur lors de la requête Airtable");

        // Act
        $this->repository->findCouponByLastFiveChars($lastFiveChars);
    }

    public function testUpdateCouponActivationSuccess(): void
    {
        // Arrange
        $recordId = "recDa96prat6HTvLB";
        $activationData = [
            "nom" => "John Doe",
            "email" => "john@example.com",
            "telephone" => "0123456789",
            "entreprise" => "ACME Inc",
            "dateActivation" => "2024-01-20T10:00:00.000Z"
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode([
                "id" => $recordId,
                "fields" => array_merge(
                    ["motif" => "Anniversaire"],
                    $activationData
                )
            ]))
        );

        // Act
        $result = $this->repository->updateCouponActivation($recordId, $activationData);

        // Assert
        $this->assertTrue($result);
    }

    public function testUpdateCouponActivationThrowsExceptionOnApiError(): void
    {
        // Arrange
        $recordId = "recDa96prat6HTvLB";
        $activationData = [
            "nom" => "John Doe",
            "email" => "john@example.com"
        ];

        $this->mockHandler->append(
            new Response(404, [], json_encode([
                "error" => "NOT_FOUND"
            ]))
        );

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Erreur lors de la mise à jour du coupon");

        // Act
        $this->repository->updateCouponActivation($recordId, $activationData);
    }
}