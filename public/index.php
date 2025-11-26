<?php

// Point d'entrée de l'application
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\Database;
use App\Infrastructure\Repository\SqlUserRepository;
use App\Infrastructure\Repository\SqlWalletRepository;
use App\Infrastructure\Service\JwtService;
use App\Application\UseCase\Auth\RegisterUserUseCase;
use App\Application\UseCase\Auth\LoginUserUseCase;
use App\Application\UseCase\Wallet\CreateWalletUseCase;
use App\Application\UseCase\Wallet\DepositMoneyUseCase;
use App\Application\UseCase\Wallet\RecordExpenseUseCase;
use App\Application\UseCase\Wallet\SetWeeklyAllowanceUseCase;
use App\Presentation\Controller\AuthController;
use App\Presentation\Controller\WalletController;

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Headers CORS (à adapter selon vos besoins)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

try {
  // Injection de dépendances (DI Container simplifié)

  // Repositories
  $userRepository = new SqlUserRepository();
  $walletRepository = new SqlWalletRepository();

  // Services
  $jwtService = new JwtService();

  // Use Cases
  $registerUserUseCase = new RegisterUserUseCase($userRepository, $walletRepository);
  $loginUserUseCase = new LoginUserUseCase($userRepository);
  $createWalletUseCase = new CreateWalletUseCase($userRepository, $walletRepository);
  $depositMoneyUseCase = new DepositMoneyUseCase($walletRepository);
  $recordExpenseUseCase = new RecordExpenseUseCase($walletRepository);
  $setWeeklyAllowanceUseCase = new SetWeeklyAllowanceUseCase($walletRepository);

  // Controllers
  $authController = new AuthController($registerUserUseCase, $loginUserUseCase, $jwtService);
  $walletController = new WalletController(
    $createWalletUseCase,
    $depositMoneyUseCase,
    $recordExpenseUseCase,
    $setWeeklyAllowanceUseCase,
    $userRepository,
    $walletRepository
  );

  // Routing avec FastRoute
  $dispatcher = require __DIR__ . '/../config/routes.php';

  // Récupérer la méthode et l'URI
  $httpMethod = $_SERVER['REQUEST_METHOD'];
  $uri = $_SERVER['REQUEST_URI'];

  // Supprimer les query strings
  if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
  }
  $uri = rawurldecode($uri);

  // Dispatcher la route
  $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

  switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
      http_response_code(404);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => 'Route not found']);
      break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
      http_response_code(405);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => 'Method not allowed']);
      break;

    case FastRoute\Dispatcher::FOUND:
      $handler = $routeInfo[1];
      $vars = $routeInfo[2];

      // Appeler le contrôleur
      [$controllerName, $method] = $handler;

      // Gérer les routes de vues HTML
      if ($controllerName === 'ViewController') {
        if ($method === 'home') {
          readfile(__DIR__ . '/index.html');
          exit;
        } elseif ($method === 'dashboard') {
          readfile(__DIR__ . '/dashboard.html');
          exit;
        }
      }

      // Gérer les routes API
      switch ($controllerName) {
        case 'AuthController':
          $authController->$method();
          break;
        case 'WalletController':
          $walletController->$method(...array_values($vars));
          break;
        default:
          http_response_code(500);
          header('Content-Type: application/json');
          echo json_encode(['success' => false, 'error' => 'Unknown controller']);
      }
      break;
  }

} catch (\Exception $e) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode([
    'success' => false,
    'error' => 'Internal server error',
    'message' => $e->getMessage() // À retirer en production
  ]);
}
