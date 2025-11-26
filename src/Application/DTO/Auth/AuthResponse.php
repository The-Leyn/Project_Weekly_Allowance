<?php

namespace App\Application\DTO\Auth;

/**
 * DTO pour la réponse d'authentification
 */
class AuthResponse
{
  public function __construct(
    public readonly int $userId,
    public readonly string $email,
    public readonly array $roles,
    public readonly bool $hasWallet,
    public readonly string $token
  ) {
  }

  public function toArray(): array
  {
    return [
      'userId' => $this->userId,
      'email' => $this->email,
      'roles' => $this->roles,
      'hasWallet' => $this->hasWallet,
      'token' => $this->token,
    ];
  }
}
