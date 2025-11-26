<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\WalletStatus;
use App\Infrastructure\Database\Database;

/**
 * Implémentation SQL du UserRepository
 */
class SqlUserRepository implements UserRepositoryInterface
{
  private \PDO $pdo;

  public function __construct()
  {
    $this->pdo = Database::getInstance()->getConnection();
  }

  public function save(User $user): void
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO users (email, password, parent_id, wallet_id, created_at)
      VALUES (:email, :password, :parent_id, :wallet_id, :created_at)
    ");

    $stmt->execute([
      'email' => $user->getEmail(),
      'password' => $user->getPassword(),
      'parent_id' => $user->getParentId(),
      'wallet_id' => $user->hasWallet() ? $user->getWalletId() : null,
      'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
    ]);

    $userId = (int) $this->pdo->lastInsertId();

    // Sauvegarder les rôles
    foreach ($user->getRoles() as $role) {
      $this->saveRole($userId, $role);
    }

    // Mettre à jour l'ID de l'utilisateur via réflexion
    $reflection = new \ReflectionClass($user);
    $property = $reflection->getProperty('id');
    $property->setAccessible(true);
    $property->setValue($user, $userId);
  }

  public function update(User $user): void
  {
    $stmt = $this->pdo->prepare("
      UPDATE users
      SET email = :email,
          password = :password,
          parent_id = :parent_id,
          wallet_id = :wallet_id
      WHERE id = :id
    ");

    $stmt->execute([
      'id' => $user->getId(),
      'email' => $user->getEmail(),
      'password' => $user->getPassword(),
      'parent_id' => $user->getParentId(),
      'wallet_id' => $user->hasWallet() ? $user->getWalletId() : null,
    ]);

    // Mettre à jour les rôles (supprimer et recréer)
    $this->deleteRoles($user->getId());
    foreach ($user->getRoles() as $role) {
      $this->saveRole($user->getId(), $role);
    }
  }

  public function findById(int $id): ?User
  {
    $stmt = $this->pdo->prepare("
      SELECT * FROM users WHERE id = :id
    ");
    $stmt->execute(['id' => $id]);
    $data = $stmt->fetch();

    if (!$data) {
      return null;
    }

    return $this->hydrate($data);
  }

  public function findByEmail(string $email): ?User
  {
    $stmt = $this->pdo->prepare("
      SELECT * FROM users WHERE email = :email
    ");
    $stmt->execute(['email' => $email]);
    $data = $stmt->fetch();

    if (!$data) {
      return null;
    }

    return $this->hydrate($data);
  }

  public function findUsersByRole(string $role): array
  {
    $stmt = $this->pdo->prepare("
      SELECT u.* FROM users u
      INNER JOIN user_roles ur ON u.id = ur.user_id
      WHERE ur.role = :role
    ");
    $stmt->execute(['role' => $role]);

    $users = [];
    while ($data = $stmt->fetch()) {
      $users[] = $this->hydrate($data);
    }

    return $users;
  }

  public function findUsersByParentId(int $parentId): array
  {
    $stmt = $this->pdo->prepare("
      SELECT * FROM users WHERE parent_id = :parent_id
    ");
    $stmt->execute(['parent_id' => $parentId]);

    $users = [];
    while ($data = $stmt->fetch()) {
      $users[] = $this->hydrate($data);
    }

    return $users;
  }

  public function delete(int $id): void
  {
    $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
  }

  private function hydrate(array $data): User
  {
    // Récupérer les rôles
    $roles = $this->getRoles((int) $data['id']);

    // Créer le WalletStatus
    $walletStatus = $data['wallet_id']
      ? WalletStatus::withWallet((int) $data['wallet_id'])
      : WalletStatus::withoutWallet();

    return new User(
      id: (int) $data['id'],
      email: $data['email'],
      password: $data['password'],
      roles: $roles,
      parentId: $data['parent_id'] ? (int) $data['parent_id'] : null,
      walletStatus: $walletStatus,
      createdAt: new \DateTimeImmutable($data['created_at'])
    );
  }

  private function getRoles(int $userId): array
  {
    $stmt = $this->pdo->prepare("
      SELECT role FROM user_roles WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);

    $roles = [];
    while ($row = $stmt->fetch()) {
      $roles[] = new Role($row['role']);
    }

    return $roles;
  }

  private function saveRole(int $userId, Role $role): void
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO user_roles (user_id, role) VALUES (:user_id, :role)
    ");
    $stmt->execute([
      'user_id' => $userId,
      'role' => $role->getValue(),
    ]);
  }

  private function deleteRoles(int $userId): void
  {
    $stmt = $this->pdo->prepare("DELETE FROM user_roles WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
  }
}
