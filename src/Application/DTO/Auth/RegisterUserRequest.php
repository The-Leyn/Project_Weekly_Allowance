<?php

namespace App\Application\DTO\Auth;

/**
 * DTO pour la requête d'inscription d'un utilisateur
 */
class RegisterUserRequest
{
  public function __construct(
    public readonly string $email,
    public readonly string $password,
    public readonly string $role,
    public readonly ?int $parentId = null
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

    if (empty($this->role)) {
      throw new \InvalidArgumentException("Role is required");
    }
  }
}
