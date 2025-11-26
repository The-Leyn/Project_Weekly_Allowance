<?php

namespace App\Domain\Entity;

/**
 * Entité Wallet - Agrégat pour la gestion du porte-monnaie
 * Séparé de User, communication via userId
 */
class Wallet
{
  private int $id;
  private int $userId;
  private int $balance;
  private int $weeklyAllowance;
  private ?\DateTimeImmutable $lastAllowanceDate;
  private \DateTimeImmutable $createdAt;

  public function __construct(
    int $id,
    int $userId,
    int $balance = 0,
    int $weeklyAllowance = 0,
    ?\DateTimeImmutable $lastAllowanceDate = null,
    ?\DateTimeImmutable $createdAt = null
  ) {
    $this->id = $id;
    $this->userId = $userId;
    $this->balance = $balance;
    $this->weeklyAllowance = $weeklyAllowance;
    $this->lastAllowanceDate = $lastAllowanceDate;
    $this->createdAt = $createdAt ?? new \DateTimeImmutable();
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getUserId(): int
  {
    return $this->userId;
  }

  public function getBalance(): int
  {
    return $this->balance;
  }

  public function getWeeklyAllowance(): int
  {
    return $this->weeklyAllowance;
  }

  public function getLastAllowanceDate(): ?\DateTimeImmutable
  {
    return $this->lastAllowanceDate;
  }

  public function getCreatedAt(): \DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function deposit(int $amount): void
  {
    // Règle : Pas de dépôt négatif
    if ($amount < 0) {
      throw new \InvalidArgumentException("Le montant du dépôt ne peut pas être négatif");
    }
    $this->balance += $amount;
  }

  public function withdraw(int $amount): void
  {
    if ($amount < 0) {
      throw new \InvalidArgumentException("Amount must be positive");
    }

    if ($amount > $this->balance) {
      throw new \DomainException("Insufficient balance");
    }

    $this->balance -= $amount;
  }

  public function setWeeklyAllowance(int $amount): void
  {
    if ($amount < 0) {
      throw new \InvalidArgumentException("Weekly allowance must be positive or zero");
    }

    $this->weeklyAllowance = $amount;
  }

  public function applyWeeklyAllowance(): void
  {
    if ($this->weeklyAllowance <= 0) {
      throw new \DomainException("Weekly allowance is not set");
    }

    $this->balance += $this->weeklyAllowance;
    $this->lastAllowanceDate = new \DateTimeImmutable();
  }
}
