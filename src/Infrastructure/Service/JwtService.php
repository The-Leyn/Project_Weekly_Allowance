<?php

namespace App\Infrastructure\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Service d'authentification JWT utilisant Firebase JWT
 */
class JwtService
{
  private string $secretKey;
  private string $algorithm = 'HS256';
  private int $expirationTime = 3600; // 1 heure

  public function __construct()
  {
    $this->secretKey = getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production';
  }

  /**
   * Générer un token JWT pour un utilisateur
   */
  public function generateToken(int $userId, string $email, array $roles): string
  {
    $issuedAt = time();
    $expirationTime = $issuedAt + $this->expirationTime;

    $payload = [
      'iat' => $issuedAt,
      'exp' => $expirationTime,
      'userId' => $userId,
      'email' => $email,
      'roles' => $roles,
    ];

    return JWT::encode($payload, $this->secretKey, $this->algorithm);
  }

  /**
   * Valider et décoder un token JWT
   * @return array|null Les données du token si valide, null sinon
   */
  public function validateToken(string $token): ?array
  {
    try {
      $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
      return (array) $decoded;
    } catch (\Exception $e) {
      return null;
    }
  }

  /**
   * Extraire l'ID utilisateur d'un token
   */
  public function getUserIdFromToken(string $token): ?int
  {
    $data = $this->validateToken($token);
    return $data['userId'] ?? null;
  }

  /**
   * Vérifier si un token est expiré
   */
  public function isTokenExpired(string $token): bool
  {
    $data = $this->validateToken($token);
    if ($data === null) {
      return true;
    }

    return time() > ($data['exp'] ?? 0);
  }
}
