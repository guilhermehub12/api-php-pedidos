<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Src\Controllers\CarroController;
use Src\Services\CarroService;
use Src\Models\Carro;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
$dotenv->required(['DB_HOST','DB_DATABASE','DB_USERNAME','DB_PASSWORD']);

$pdo = require __DIR__ . '/../configs/db.php';
$logger = new Logger('carro');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Level::Debug));

// Instancia do Carro
$carro = new Carro($pdo);
$carroService = new CarroService($carro, $logger);
$carroController = new CarroController($carroService);

// Rota Carros
$method = $_SERVER['REQUEST_METHOD'];
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if (preg_match('#^carros(?:/(\d+))?$#', $uri, $matches)) {
    $id = $matches[1] ?? null;

    switch ($method) {
        case 'GET':
            if ($id) {
                // /carros/{id}
                $carroController->obterPorId((int)$id);
            } else {
                // /carros
                $carroController->listar($_GET);
            }
            break;

        case 'POST':
            // /carros
            $carroController->criar();
            break;

        case 'PUT':
            // /carros/{id}
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do carro não fornecido']);
                exit;
            }
            $carroController->atualizar((int)$id);
            break;

        case 'DELETE':
            // /carros/{id}
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do carro não fornecido']);
                exit;
            }
            $carroController->deletar((int)$id);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            header('Allow: GET, POST, PUT, DELETE');
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Rota não encontrada']);
}