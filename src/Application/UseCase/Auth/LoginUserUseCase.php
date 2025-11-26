<?php

namespace App\Application\UseCase\Auth;

use App\Application\DTO\Auth\LoginRequest;
use App\Application\DTO\Auth\AuthResponse;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Role;

/**
 * Use Case pour la connexion d'un utilisateur
 */
class LoginUserUseCase
{
  public function __construct(
    private UserRepositoryInterface $userRepository
  ) {
  }

  public function execute(LoginRequest $request): AuthResponse
  {
    // 1. Trouver l'utilisateur par email
    $user = $this->userRepository->findByEmail($request->email);
    if ($user === null) {
      throw new \DomainException("Invalid credentials");
    }

    // 2. Vérifier le mot de passe
    if (!password_verify($request->password, $user->getPassword())) {
      throw new \DomainException("Invalid credentials");
    }

    // 3. Générer un token JWT
    $token = $this->generateToken($user->getEmail());

    // 4. Retourner la réponse
    return new AuthResponse(
      userId: $user->getId(),
      email: $user->getEmail(),
      roles: array_map(fn(Role $r) => $r->getValue(), $user->getRoles()),
      hasWallet: $user->hasWallet(),
      token: $token
    );
  }

  private function generateToken(string $email): string
  {
    // Pour le moment, un token simple
    // Sera remplacé par Firebase JWT dans l'infrastructure
    return base64_encode($email . ':' . time());
  }
}
