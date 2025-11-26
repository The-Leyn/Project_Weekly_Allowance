<?php

namespace App\Application\DTO\Auth;

/**
 * DTO pour la requête de connexion
 */
class LoginRequest
{
  public function __construct(
    public readonly string $email,
    public readonly string $password
  ) {
    $this->validate();
  }

  private function validate(): void
  {
    if (empty($this->email)) {
      throw new \InvalidArgumentException("Email is required");
    }

    if (empty($this->password)) {
      throw new \InvalidArgumentException("Password is required");
    }
  }
}
