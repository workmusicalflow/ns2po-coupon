<?php

namespace Middleware;

class ErrorHandler
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function handleError($errno, $errstr, $errfile, $errline)
    {
        $error = [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];

        $this->logger->error("PHP Error: {$errstr}", $error);

        if (getenv('APP_ENV') === 'development') {
            return false; // Let PHP handle the error in development
        }

        $this->jsonResponse([
            'error' => 'Une erreur technique est survenue'
        ], 500);
        return true;
    }

    public function handleException($exception)
    {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        $this->logger->error("Uncaught Exception: {$exception->getMessage()}", $error);

        $statusCode = 500;
        $message = 'Une erreur technique est survenue';

        if ($exception instanceof \InvalidArgumentException) {
            $statusCode = 400;
            $message = $exception->getMessage();
        }

        if (getenv('APP_ENV') === 'development') {
            $message = $exception->getMessage();
        }

        $this->jsonResponse([
            'error' => $message
        ], $statusCode);
    }

    private function jsonResponse(array $data, int $statusCode = 500): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}