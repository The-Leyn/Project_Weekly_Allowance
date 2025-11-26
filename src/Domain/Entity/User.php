<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\WalletStatus;

/**
 * Entité User - Agrégat principal pour les utilisateurs
 * Utilise la composition avec des rôles au lieu de l'héritage
 */
class User
{
  private int $id;
  private string $email;
  private string $password;
  /** @var Role[] */
  private array $roles;
  private ?int $parentId;
  private WalletStatus $walletStatus;
  private \DateTimeImmutable $createdAt;

  public function __construct(
    int $id,
    string $email,
    string $password,
    array $roles,
    ?int $parentId,
    WalletStatus $walletStatus,
    ?\DateTimeImmutable $createdAt = null
  ) {
    $this->validateEmail($email);
    $this->validatePassword($password);
    $this->validateRoles($roles);
    $this->validateParentId($roles, $parentId);

    $this->id = $id;
    $this->email = $email;
    $this->password = $password;
    $this->roles = $roles;
    $this->parentId = $parentId;
    $this->walletStatus = $walletStatus;
    $this->createdAt = $createdAt ?? new \DateTimeImmutable();
  }

  private function validateEmail(string $email): void
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new \InvalidArgumentException("Invalid email format");
    }
  }

  private function validatePassword(string $password): void
  {
    if (empty($password)) {
      throw new \InvalidArgumentException("Password cannot be empty");
    }
  }

  private function validateRoles(array $roles): void
  {
    if (empty($roles)) {
      throw new \InvalidArgumentException("User must have at least one role");
    }

    foreach ($roles as $role) {
      if (!$role instanceof Role) {
        throw new \InvalidArgumentException("All roles must be instances of Role");
      }
    }
  }

  private function validateParentId(array $roles, ?int $parentId): void
  {
    $isTeen = false;
    $isParent = false;

    foreach ($roles as $role) {
      if ($role->isTeen()) {
        $isTeen = true;
      }
      if ($role->isParent()) {
        $isParent = true;
      }
    }

    if ($isTeen && $parentId === null) {
      throw new \InvalidArgumentException("Teen must have a parent ID");
    }

    if ($isParent && $parentId !== null) {
      throw new \InvalidArgumentException("Parent cannot have a parent ID");
    }
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getPassword(): string
  {
    return $this->password;
  }

  public function getRoles(): array
  {
    return $this->roles;
  }

  public function getParentId(): ?int
  {
    return $this->parentId;
  }

  public function getWalletStatus(): WalletStatus
  {
    return $this->walletStatus;
  }

  public function getCreatedAt(): \DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function hasRole(string $roleValue): bool
  {
    foreach ($this->roles as $role) {
      if ($role->getValue() === $roleValue) {
        return true;
      }
    }
    return false;
  }

  public function addRole(Role $newRole): void
  {
    // Vérifier si le rôle existe déjà
    foreach ($this->roles as $role) {
      if ($role->equals($newRole)) {
        return; // Rôle déjà présent
      }
    }

    $this->roles[] = $newRole;
  }

  public function removeRole(Role $roleToRemove): void
  {
    $this->roles = array_filter($this->roles, function (Role $role) use ($roleToRemove) {
      return !$role->equals($roleToRemove);
    });

    // Réindexer le tableau
    $this->roles = array_values($this->roles);
  }

  public function isParent(): bool
  {
    return $this->hasRole(Role::PARENT);
  }

  public function isTeen(): bool
  {
    return $this->hasRole(Role::TEEN);
  }

  public function isAdmin(): bool
  {
    return $this->hasRole(Role::ADMIN);
  }

  public function hasWallet(): bool
  {
    return $this->walletStatus->hasWallet();
  }

  public function getWalletId(): int
  {
    return $this->walletStatus->getWalletId();
  }

  public function setWalletStatus(WalletStatus $walletStatus): void
  {
    $this->walletStatus = $walletStatus;
  }
}
