<?php

namespace Utils;

class Logger
{
    private string $logPath;
    private array $levels = ['INFO', 'ERROR', 'WARNING', 'DEBUG'];

    public function __construct()
    {
        $this->logPath = __DIR__ . '/../../logs';
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0777, true);
        }
    }

    public function log(string $level, string $message, array $context = []): void
    {
        if (!in_array(strtoupper($level), $this->levels)) {
            throw new \InvalidArgumentException('Invalid log level');
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$level}: {$message} {$contextStr}\n";

        $filename = $this->logPath . '/' . date('Y-m-d') . '.log';
        file_put_contents($filename, $logMessage, FILE_APPEND);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        if (getenv('APP_ENV') === 'development') {
            $this->log('DEBUG', $message, $context);
        }
    }
}