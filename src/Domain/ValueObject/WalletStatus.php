<?php

namespace App\Domain\ValueObject;

/**
 * Value Object représentant le statut du wallet d'un utilisateur
 * Immutable et type-safe
 */
class WalletStatus
{
  private bool $hasWallet;
  private ?int $walletId;

  private function __construct(bool $hasWallet, ?int $walletId)
  {
    $this->hasWallet = $hasWallet;
    $this->walletId = $walletId;
  }

  public static function withoutWallet(): self
  {
    return new self(false, null);
  }

  public static function withWallet(int $walletId): self
  {
    if ($walletId <= 0) {
      throw new \InvalidArgumentException("Wallet ID must be positive");
    }

    return new self(true, $walletId);
  }

  public function hasWallet(): bool
  {
    return $this->hasWallet;
  }

  public function getWalletId(): int
  {
    if (!$this->hasWallet) {
      throw new \DomainException("User has no wallet");
    }

    return $this->walletId;
  }

  public function equals(WalletStatus $other): bool
  {
    return $this->hasWallet === $other->hasWallet
      && $this->walletId === $other->walletId;
  }
}
