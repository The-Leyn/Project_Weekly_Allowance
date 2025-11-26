<?php

namespace App\Application\DTO\Wallet;

/**
 * DTO pour la réponse contenant les informations d'un wallet
 */
class WalletResponse
{
  public function __construct(
    public readonly int $id,
    public readonly int $userId,
    public readonly int $balance,
    public readonly int $weeklyAllowance,
    public readonly ?\DateTimeImmutable $lastAllowanceDate
  ) {
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'userId' => $this->userId,
      'balance' => $this->balance,
      'weeklyAllowance' => $this->weeklyAllowance,
      'lastAllowanceDate' => $this->lastAllowanceDate?->format('Y-m-d H:i:s'),
    ];
  }
}
