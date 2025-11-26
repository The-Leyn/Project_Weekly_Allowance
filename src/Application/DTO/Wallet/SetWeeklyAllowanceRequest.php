<?php

namespace App\Application\DTO\Wallet;

/**
 * DTO pour la requête de définition d'allocation hebdomadaire
 */
class SetWeeklyAllowanceRequest
{
  public function __construct(
    public readonly int $walletId,
    public readonly int $amount
  ) {
    $this->validate();
  }

  private function validate(): void
  {
    if ($this->amount < 0) {
      throw new \InvalidArgumentException("Weekly allowance cannot be negative");
    }
  }
}
