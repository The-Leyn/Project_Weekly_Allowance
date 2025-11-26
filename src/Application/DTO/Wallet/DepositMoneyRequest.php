<?php

namespace App\Application\DTO\Wallet;

/**
 * DTO pour la requête de dépôt d'argent
 */
class DepositMoneyRequest
{
  public function __construct(
    public readonly int $walletId,
    public readonly int $amount
  ) {
    $this->validate();
  }

  private function validate(): void
  {
    if ($this->amount <= 0) {
      throw new \InvalidArgumentException("Amount must be positive");
    }
  }
}
