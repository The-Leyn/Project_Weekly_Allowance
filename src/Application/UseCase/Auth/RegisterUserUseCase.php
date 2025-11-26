<?php

namespace App\Application\UseCase\Auth;

use App\Application\DTO\Auth\RegisterUserRequest;
use App\Application\DTO\Auth\AuthResponse;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\WalletRepositoryInterface;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\WalletStatus;

/**
 * Use Case pour l'inscription d'un utilisateur
 */
class RegisterUserUseCase
{
  public function __construct(
    private UserRepositoryInterface $userRepository,
    private WalletRepositoryInterface $walletRepository
  ) {
  }

  public function execute(RegisterUserRequest $request): AuthResponse
  {
    // 1. Vérifier que l'email n'existe pas déjà
    $existingUser = $this->userRepository->findByEmail($request->email);
    if ($existingUser !== null) {
      throw new \DomainException("Email already exists");
    }

    // 2. Valider et créer le rôle
    $role = new Role($request->role);

    // 3. Si c'est un teen, vérifier que le parent existe
    if ($role->isTeen()) {
      if ($request->parentId === null) {
        throw new \DomainException("Teen must have a parent ID");
      }

      $parent = $this->userRepository->findById($request->parentId);
      if ($parent === null) {
        throw new \DomainException("Parent not found");
      }
    }

    // 4. Hasher le mot de passe
    $hashedPassword = password_hash($request->password, PASSWORD_DEFAULT);

    // 5. Créer l'utilisateur
    $user = new User(
      id: 0, // Sera défini par le repository lors de la sauvegarde
      email: $request->email,
      password: $hashedPassword,
      roles: [$role],
      parentId: $request->parentId,
      walletStatus: WalletStatus::withoutWallet()
    );

    // 6. Sauvegarder l'utilisateur
    $this->userRepository->save($user);

    // 7. Si c'est un teen, récupérer le wallet_id du parent (wallet partagé)
    if ($role->isTeen()) {
      $parent = $this->userRepository->findById($request->parentId);

      // Si le parent a un wallet, le teen partage le même wallet
      if ($parent->hasWallet()) {
        $user->setWalletStatus(WalletStatus::withWallet($parent->getWalletId()));
        $this->userRepository->update($user);
      }
      // Sinon, le teen n'a pas encore de wallet (le parent devra en créer un)
    }

    // 8. Générer un token JWT (simplifié pour le moment)
    $token = $this->generateToken($user);

    // 9. Retourner la réponse
    return new AuthResponse(
      userId: $user->getId(),
      email: $user->getEmail(),
      roles: array_map(fn(Role $r) => $r->getValue(), $user->getRoles()),
      hasWallet: $user->hasWallet(),
      token: $token
    );
  }

  private function generateToken(User $user): string
  {
    // Pour le moment, un token simple
    // Sera remplacé par Firebase JWT dans l'infrastructure
    return base64_encode($user->getEmail() . ':' . time());
  }
}
