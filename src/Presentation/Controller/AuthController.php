<?php

namespace App\Presentation\Controller;

use App\Application\DTO\Auth\RegisterUserRequest;
use App\Application\DTO\Auth\LoginRequest;
use App\Application\UseCase\Auth\RegisterUserUseCase;
use App\Application\UseCase\Auth\LoginUserUseCase;
use App\Infrastructure\Service\JwtService;

/**
 * Contrôleur d'authentification
 */
class AuthController
{
  public function __construct(
    private RegisterUserUseCase $registerUserUseCase,
    private LoginUserUseCase $loginUserUseCase,
    private JwtService $jwtService
  ) {
  }

  /**
   * Inscription d'un utilisateur (parent ou teen)
   */
  public function register(): void
  {
    try {
      // Récupérer les données JSON
      $data = json_decode(file_get_contents('php://input'), true);

      // Créer le DTO
      $request = new RegisterUserRequest(
        email: $data['email'] ?? '',
        password: $data['password'] ?? '',
        role: $data['role'] ?? '',
        parentId: $data['parentId'] ?? null
      );

      // Exécuter le use case
      $response = $this->registerUserUseCase->execute($request);

      // Définir le cookie JWT (HTTP-only pour la sécurité)
      setcookie(
        'auth_token',
        $response->token,
        [
          'expires' => time() + 3600,
          'path' => '/',
          'httponly' => true,
          'secure' => false, // true en production avec HTTPS
          'samesite' => 'Lax'
        ]
      );

      // Retourner la réponse JSON
      http_response_code(201);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'data' => $response->toArray()
      ]);
    } catch (\InvalidArgumentException $e) {
      http_response_code(400);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    } catch (\DomainException $e) {
      http_response_code(409);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    } catch (\Exception $e) {
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
      ]);
    }
  }

  /**
   * Connexion d'un utilisateur
   */
  public function login(): void
  {
    try {
      // Récupérer les données JSON
      $data = json_decode(file_get_contents('php://input'), true);

      // Créer le DTO
      $request = new LoginRequest(
        email: $data['email'] ?? '',
        password: $data['password'] ?? ''
      );

      // Exécuter le use case
      $response = $this->loginUserUseCase->execute($request);

      // Définir le cookie JWT
      setcookie(
        'auth_token',
        $response->token,
        [
          'expires' => time() + 3600,
          'path' => '/',
          'httponly' => true,
          'secure' => false,
          'samesite' => 'Lax'
        ]
      );

      // Retourner la réponse JSON
      http_response_code(200);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'data' => $response->toArray()
      ]);
    } catch (\DomainException $e) {
      http_response_code(401);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
    } catch (\Exception $e) {
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
      ]);
    }
  }

  /**
   * Déconnexion d'un utilisateur
   */
  public function logout(): void
  {
    // Supprimer le cookie
    setcookie(
      'auth_token',
      '',
      [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true
      ]
    );

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'message' => 'Logged out successfully'
    ]);
  }
}
