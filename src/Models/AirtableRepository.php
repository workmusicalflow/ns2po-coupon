<?php

namespace Models;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Utils\Logger;

class AirtableRepository
{
    private string $baseUrl;
    private array $headers;
    private Logger $logger;

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function __construct(
        private readonly ClientInterface $client,
        string $apiKey = null,
        string $baseId = null,
        string $tableName = null
    ) {
        $this->logger = new Logger();
        // Allow injection for testing but provide defaults for production
        $this->baseUrl = sprintf(
            'https://api.airtable.com/v0/%s/%s',
            $baseId ?? $_ENV['AIRTABLE_BASE_ID'],
            rawurlencode($tableName ?? $_ENV['AIRTABLE_TABLE_NAME'])
        );

        $this->headers = [
            'Authorization' => 'Bearer ' . ($apiKey ?? $_ENV['AIRTABLE_API_KEY']),
            'Content-Type' => 'application/json',
        ];
    }

    public function findCouponByLastFiveChars(string $lastFiveChars): ?array
    {
        try {
            $requestParams = [
                'headers' => $this->headers,
                'query' => [
                    'filterByFormula' => "RIGHT({RecordId}, 5) = '{$lastFiveChars}'",
                    'maxRecords' => 1
                ]
            ];
            $this->logger->debug("Démarrage de la requête Airtable", [
                'url' => $this->baseUrl,
                'filterFormula' => $requestParams['query']['filterByFormula'],
                'headers' => array_keys($this->headers)
            ]);

            $response = $this->client->request('GET', $this->baseUrl, $requestParams);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            if ($statusCode !== 200) {
                $this->logger->error("Échec de la requête Airtable", [
                    'statusCode' => $statusCode,
                    'response' => $responseBody,
                    'headers' => $response->getHeaders()
                ]);
                throw new \RuntimeException("Erreur lors de la requête Airtable");
            }

            $data = json_decode($responseBody, true);

            $this->logger->debug("Réponse Airtable reçue", [
                'recordCount' => count($data['records'] ?? []),
                'hasMatch' => !empty($data['records'])
            ]);
            $records = $data['records'] ?? [];

            return !empty($records) ? $records[0] : null;
        } catch (GuzzleException $e) {
            $message = $e->getMessage();
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->getResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $this->logger->error("Exception Airtable avec réponse", [
                    'message' => $message,
                    'response' => $responseBody
                ]);
            } else {
                $this->logger->error("Exception Airtable", [
                    'message' => $message
                ]);
            }
            throw new \RuntimeException("Erreur lors de la requête Airtable: $message", 0, $e);
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
                $errorMessage = $responseBody['error']['message'] ?? 'Unknown error';
                $this->logger->error("Erreur mise à jour coupon", [
                    'recordId' => $recordId,
                    'error' => $errorMessage
                ]);
                $message .= " - " . $errorMessage;
            }
            throw new \RuntimeException("Erreur lors de la mise à jour du coupon: " . $message, 0, $e);
        }
    }
}
