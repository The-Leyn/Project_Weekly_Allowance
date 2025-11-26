<?php

namespace App\Domain\Entity;

class Wallet
{
  private int $teenId;
  private int $balance;

  public function __construct(int $teenId, int $balance = 0)
  {
    $this->teenId = $teenId;
    $this->balance = $balance;
  }

  public function getTeenId(): int
  {
    return $this->teenId;
  }

  public function getBalance(): int
  {
    return $this->balance;
  }

  public function deposit(int $amount): void
  {
    // Règle : Pas de dépôt négatif
    if ($amount < 0) {
      throw new \InvalidArgumentException("Le montant du dépôt ne peut pas être négatif");
    }
    $this->balance += $amount;
  }
}
