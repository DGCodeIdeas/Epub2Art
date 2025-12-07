<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\ConversionController;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->post('/upload', [ConversionController::class, 'upload']);
$router->get('/result', [ConversionController::class, 'result']);

$router->resolve();
