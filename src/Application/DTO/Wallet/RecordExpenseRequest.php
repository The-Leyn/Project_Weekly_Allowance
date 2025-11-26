<?php

namespace App\Application\DTO\Wallet;

/**
 * DTO pour la requête d'enregistrement de dépense
 */
class RecordExpenseRequest
{
  public function __construct(
    public readonly int $walletId,
    public readonly int $amount,
    public readonly string $description = ''
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
