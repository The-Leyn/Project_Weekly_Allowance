<?php

namespace Tests\Unit\ValueObject;

use App\Domain\ValueObject\WalletStatus;
use PHPUnit\Framework\TestCase;

class WalletStatusTest extends TestCase
{
  // Test 1 : Vérifier qu'on peut créer un statut sans wallet
  public function test_can_create_status_without_wallet()
  {
    $status = WalletStatus::withoutWallet();

    $this->assertFalse($status->hasWallet());
  }

  // Test 2 : Vérifier qu'on peut créer un statut avec wallet
  public function test_can_create_status_with_wallet()
  {
    $walletId = 42;
    $status = WalletStatus::withWallet($walletId);

    $this->assertTrue($status->hasWallet());
    $this->assertEquals($walletId, $status->getWalletId());
  }

  // Test 3 : Vérifier qu'on ne peut pas obtenir walletId sans wallet
  public function test_cannot_get_wallet_id_without_wallet()
  {
    $status = WalletStatus::withoutWallet();

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage("User has no wallet");

    $status->getWalletId();
  }

  // Test 4 : Vérifier l'immutabilité (pas de setter)
  public function test_wallet_status_is_immutable()
  {
    $status = WalletStatus::withWallet(1);

    // Vérifier qu'il n'y a pas de méthode setWalletId ou setHasWallet
    $this->assertFalse(method_exists($status, 'setWalletId'));
    $this->assertFalse(method_exists($status, 'setHasWallet'));
  }

  // Test 5 : Vérifier que withWallet n'accepte pas d'ID négatif
  public function test_cannot_create_wallet_status_with_negative_id()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Wallet ID must be positive");

    WalletStatus::withWallet(-1);
  }

  // Test 6 : Vérifier que withWallet n'accepte pas zéro
  public function test_cannot_create_wallet_status_with_zero_id()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Wallet ID must be positive");

    WalletStatus::withWallet(0);
  }

  // Test 7 : Vérifier la méthode equals() pour deux statuts sans wallet
  public function test_equals_returns_true_for_two_statuses_without_wallet()
  {
    $status1 = WalletStatus::withoutWallet();
    $status2 = WalletStatus::withoutWallet();

    $this->assertTrue($status1->equals($status2));
  }

  // Test 8 : Vérifier la méthode equals() pour deux statuts avec même wallet
  public function test_equals_returns_true_for_same_wallet_id()
  {
    $status1 = WalletStatus::withWallet(42);
    $status2 = WalletStatus::withWallet(42);

    $this->assertTrue($status1->equals($status2));
  }

  // Test 9 : Vérifier la méthode equals() retourne false pour des wallets différents
  public function test_equals_returns_false_for_different_wallet_ids()
  {
    $status1 = WalletStatus::withWallet(42);
    $status2 = WalletStatus::withWallet(99);

    $this->assertFalse($status1->equals($status2));
  }

  // Test 10 : Vérifier la méthode equals() retourne false pour avec/sans wallet
  public function test_equals_returns_false_for_with_and_without_wallet()
  {
    $status1 = WalletStatus::withWallet(42);
    $status2 = WalletStatus::withoutWallet();

    $this->assertFalse($status1->equals($status2));
  }
}
