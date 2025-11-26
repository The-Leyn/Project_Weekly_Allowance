<?php

namespace Tests\Unit;

use App\Domain\Entity\Wallet;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
  // Test 1 : Vérifier qu'on peut créer un portefeuille
  public function test_wallet_can_be_created_with_initial_balance()
  {
    $teenId = 123;
    $initialBalance = 5000;
    $wallet = new Wallet($teenId, $initialBalance);

    $this->assertEquals($teenId, $wallet->getTeenId());
    $this->assertEquals($initialBalance, $wallet->getBalance());
  }

  // Test 2 : Vérifier qu'on peut ajouter de l'argent
  public function test_money_can_be_deposited_into_wallet()
  {
    $wallet = new Wallet(teenId: 1, balance: 1000);
    $wallet->deposit(500);

    $this->assertEquals(1500, $wallet->getBalance());
  }

  // Test 3 : Vérifier que on ne peut pas faire un dépot négatif
  public function test_cannot_add_negative_amount()
  {
    $wallet = new Wallet(1, 0);

    $this->expectException(\InvalidArgumentException::class);

    $wallet->deposit(-50);
  }
}
