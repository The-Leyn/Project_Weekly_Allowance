<?php

namespace App\Application\DTO\Wallet;

/**
 * DTO pour la requête de création de wallet
 */
class CreateWalletRequest
{
  public function __construct(
    public readonly int $userId,
    public readonly int $initialBalance = 0
  ) {
    $this->validate();
  }

  private function validate(): void
  {
    if ($this->initialBalance < 0) {
      throw new \InvalidArgumentException("Initial balance cannot be negative");
    }
  }
}
