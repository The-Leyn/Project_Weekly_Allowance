<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Wallet;
use App\Domain\Repository\WalletRepositoryInterface;
use App\Infrastructure\Database\Database;

/**
 * Implémentation SQL du WalletRepository
 */
class SqlWalletRepository implements WalletRepositoryInterface
{
  private \PDO $pdo;

  public function __construct()
  {
    $this->pdo = Database::getInstance()->getConnection();
  }

  public function save(Wallet $wallet): void
  {
    $stmt = $this->pdo->prepare("
      INSERT INTO wallets (user_id, balance, weekly_allowance, last_allowance_date, created_at)
      VALUES (:user_id, :balance, :weekly_allowance, :last_allowance_date, :created_at)
    ");

    $stmt->execute([
      'user_id' => $wallet->getUserId(),
      'balance' => $wallet->getBalance(),
      'weekly_allowance' => $wallet->getWeeklyAllowance(),
      'last_allowance_date' => $wallet->getLastAllowanceDate()?->format('Y-m-d H:i:s'),
      'created_at' => $wallet->getCreatedAt()->format('Y-m-d H:i:s'),
    ]);

    $walletId = (int) $this->pdo->lastInsertId();

    // Mettre à jour l'ID du wallet via réflexion
    $reflection = new \ReflectionClass($wallet);
    $property = $reflection->getProperty('id');
    $property->setAccessible(true);
    $property->setValue($wallet, $walletId);
  }

  public function update(Wallet $wallet): void
  {
    $stmt = $this->pdo->prepare("
      UPDATE wallets
      SET balance = :balance,
          weekly_allowance = :weekly_allowance,
          last_allowance_date = :last_allowance_date
      WHERE id = :id
    ");

    $stmt->execute([
      'id' => $wallet->getId(),
      'balance' => $wallet->getBalance(),
      'weekly_allowance' => $wallet->getWeeklyAllowance(),
      'last_allowance_date' => $wallet->getLastAllowanceDate()?->format('Y-m-d H:i:s'),
    ]);
  }

  public function findById(int $id): ?Wallet
  {
    $stmt = $this->pdo->prepare("
      SELECT * FROM wallets WHERE id = :id
    ");
    $stmt->execute(['id' => $id]);
    $data = $stmt->fetch();

    if (!$data) {
      return null;
    }

    return $this->hydrate($data);
  }

  public function findByUserId(int $userId): ?Wallet
  {
    $stmt = $this->pdo->prepare("
      SELECT * FROM wallets WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
    $data = $stmt->fetch();

    if (!$data) {
      return null;
    }

    return $this->hydrate($data);
  }

  public function delete(int $id): void
  {
    $stmt = $this->pdo->prepare("DELETE FROM wallets WHERE id = :id");
    $stmt->execute(['id' => $id]);
  }

  private function hydrate(array $data): Wallet
  {
    return new Wallet(
      id: (int) $data['id'],
      userId: (int) $data['user_id'],
      balance: (int) $data['balance'],
      weeklyAllowance: (int) $data['weekly_allowance'],
      lastAllowanceDate: $data['last_allowance_date']
      ? new \DateTimeImmutable($data['last_allowance_date'])
      : null,
      createdAt: new \DateTimeImmutable($data['created_at'])
    );
  }
}
