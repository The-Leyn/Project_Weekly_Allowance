<?php

namespace Tests\Unit\Entity;

use App\Domain\Entity\Wallet;
use PHPUnit\Framework\TestCase;

class WalletTest extends TestCase
{
  // ===== Tests existants (déplacés) =====

  // Test 1 : Vérifier qu'on peut créer un portefeuille
  public function test_wallet_can_be_created_with_initial_balance()
  {
    $userId = 123;
    $initialBalance = 5000;
    $wallet = new Wallet(
      id: 1,
      userId: $userId,
      balance: $initialBalance
    );

    $this->assertEquals($userId, $wallet->getUserId());
    $this->assertEquals($initialBalance, $wallet->getBalance());
  }

  // Test 2 : Vérifier qu'on peut ajouter de l'argent
  public function test_money_can_be_deposited_into_wallet()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 1000);
    $wallet->deposit(500);

    $this->assertEquals(1500, $wallet->getBalance());
  }

  // Test 3 : Vérifier que on ne peut pas faire un dépôt négatif
  public function test_cannot_add_negative_amount()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 0);

    $this->expectException(\InvalidArgumentException::class);

    $wallet->deposit(-50);
  }

  // ===== Nouveaux tests =====

  // Test 4 : Vérifier qu'on peut retirer de l'argent
  public function test_can_withdraw_money()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 1000);
    $wallet->withdraw(300);

    $this->assertEquals(700, $wallet->getBalance());
  }

  // Test 5 : Vérifier qu'on ne peut pas retirer plus que le solde
  public function test_cannot_withdraw_more_than_balance()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 100);

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage("Insufficient balance");

    $wallet->withdraw(200);
  }

  // Test 6 : Vérifier qu'on ne peut pas retirer un montant négatif
  public function test_cannot_withdraw_negative_amount()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 1000);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Amount must be positive");

    $wallet->withdraw(-50);
  }

  // Test 7 : Vérifier qu'on peut définir une allocation hebdomadaire
  public function test_can_set_weekly_allowance()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 0);
    $wallet->setWeeklyAllowance(500);

    $this->assertEquals(500, $wallet->getWeeklyAllowance());
  }

  // Test 8 : Vérifier qu'on ne peut pas définir une allocation négative
  public function test_cannot_set_negative_weekly_allowance()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 0);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Weekly allowance must be positive or zero");

    $wallet->setWeeklyAllowance(-100);
  }

  // Test 9 : Vérifier qu'on peut appliquer l'allocation hebdomadaire
  public function test_can_apply_weekly_allowance()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 1000, weeklyAllowance: 500);
    $wallet->applyWeeklyAllowance();

    $this->assertEquals(1500, $wallet->getBalance());
  }

  // Test 10 : Vérifier que la date de dernière allocation est mise à jour
  public function test_last_allowance_date_is_updated_when_applying()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 1000, weeklyAllowance: 500);

    $beforeDate = $wallet->getLastAllowanceDate();
    sleep(1); // Attendre 1 seconde pour voir la différence
    $wallet->applyWeeklyAllowance();
    $afterDate = $wallet->getLastAllowanceDate();

    $this->assertNotNull($afterDate);
    $this->assertNotEquals($beforeDate, $afterDate);
  }

  // Test 11 : Vérifier qu'on ne peut pas appliquer l'allocation si elle est à 0
  public function test_cannot_apply_zero_weekly_allowance()
  {
    $wallet = new Wallet(id: 1, userId: 1, balance: 1000, weeklyAllowance: 0);

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage("Weekly allowance is not set");

    $wallet->applyWeeklyAllowance();
  }

  // Test 12 : Vérifier qu'on peut créer un wallet avec allocation
  public function test_can_create_wallet_with_weekly_allowance()
  {
    $wallet = new Wallet(
      id: 1,
      userId: 1,
      balance: 1000,
      weeklyAllowance: 500
    );

    $this->assertEquals(500, $wallet->getWeeklyAllowance());
    $this->assertNull($wallet->getLastAllowanceDate());
  }

  // Test 13 : Vérifier le getter getId()
  public function test_can_get_wallet_id()
  {
    $wallet = new Wallet(id: 42, userId: 1, balance: 0);

    $this->assertEquals(42, $wallet->getId());
  }
}
