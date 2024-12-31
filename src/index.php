<?php

// Configuration du fuseau horaire pour correspondre à l'heure de la Côte d'Ivoire
ini_set('date.timezone', 'GMT');

use Dotenv\Dotenv;
use Middleware\ErrorHandler;
use Utils\Logger;

require __DIR__ . '/../vendor/autoload.php';

// Initialize logger and error handler
$logger = new Logger();
$errorHandler = new ErrorHandler($logger);

// Set error and exception handlers
set_error_handler([$errorHandler, 'handleError']);
set_exception_handler([$errorHandler, 'handleException']);

// Load environment variables
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    $dotenv->required(['AIRTABLE_API_KEY', 'AIRTABLE_BASE_ID', 'AIRTABLE_TABLE_NAME']);
} catch (\Exception $e) {
    http_response_code(500);
    if (getenv('APP_ENV') === 'development') {
        die('Erreur de configuration: ' . $e->getMessage());
    } else {
        die('Une erreur de configuration est survenue.');
    }
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_ENV') === 'development' ? '1' : '0');

// Set headers for security
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$basePath = '/src';

// Remove base path from request
$request = str_replace($basePath, '', $request);

// Route to appropriate controller/view
switch ($request) {
    case '':
    case '/':
        require __DIR__ . '/Views/index.html';
        break;
    case '/validate-coupon':
        $controller = new Controllers\CouponController();
        $controller->validateCoupon();
        break;
    case '/activate-coupon':
        $controller = new Controllers\CouponController();
        $controller->activateCoupon();
        break;
    default:
        http_response_code(404);
        echo '404 Not Found';
        break;
}
