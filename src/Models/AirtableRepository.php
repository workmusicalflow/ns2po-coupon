<?php

namespace Models;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class AirtableRepository
{
    private string $baseUrl;
    private array $headers;

    public function __construct(
        private readonly ClientInterface $client,
        string $apiKey = null,
        string $baseId = null,
        string $tableName = null
    ) {
        // Allow injection for testing but provide defaults for production
        $this->baseUrl = sprintf(
            'https://api.airtable.com/v0/%s/%s',
            $baseId ?? $_ENV['AIRTABLE_BASE_ID'],
            $tableName ?? $_ENV['AIRTABLE_TABLE_NAME']
        );

        $this->headers = [
            'Authorization' => 'Bearer ' . ($apiKey ?? $_ENV['AIRTABLE_API_KEY']),
            'Content-Type' => 'application/json',
        ];
    }

    public function findCouponByLastFiveChars(string $lastFiveChars): ?array
    {
        try {
            $response = $this->client->request('GET', $this->baseUrl, [
                'headers' => $this->headers,
                'query' => [
                    'filterByFormula' => "RIGHT(RECORD_ID(), 5) = '{$lastFiveChars}'",
                    'maxRecords' => 1
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException("Erreur lors de la requête Airtable");
            }

            $data = json_decode($response->getBody()->getContents(), true);
            $records = $data['records'] ?? [];

            return !empty($records) ? $records[0] : null;
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Erreur lors de la requête Airtable", 0, $e);
        }
    }

    public function updateCouponActivation(string $recordId, array $activationData): bool
    {
        try {
            $response = $this->client->request('PATCH', "{$this->baseUrl}/{$recordId}", [
                'headers' => $this->headers,
                'json' => [
                    'fields' => $activationData
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException("Erreur lors de la mise à jour du coupon");
            }

            return true;
        } catch (GuzzleException $e) {
            $message = $e->getMessage();
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->getResponse()) {
                $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $message .= " - " . ($responseBody['error']['message'] ?? 'Unknown error');
            }
            throw new \RuntimeException("Erreur lors de la mise à jour du coupon: " . $message, 0, $e);
        }
    }
}
