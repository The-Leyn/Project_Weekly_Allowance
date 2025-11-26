<?php

namespace Tests\Unit\Entity;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\WalletStatus;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
  // Test 1 : Vérifier qu'on peut créer un utilisateur parent
  public function test_can_create_parent_user()
  {
    $user = new User(
      id: 1,
      email: 'parent@example.com',
      password: 'hashed_password',
      roles: [new Role(Role::PARENT)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );

    $this->assertEquals(1, $user->getId());
    $this->assertEquals('parent@example.com', $user->getEmail());
    $this->assertTrue($user->hasRole(Role::PARENT));
    $this->assertFalse($user->hasWallet());
  }

  // Test 2 : Vérifier qu'on peut créer un utilisateur teen
  public function test_can_create_teen_user()
  {
    $user = new User(
      id: 2,
      email: 'teen@example.com',
      password: 'hashed_password',
      roles: [new Role(Role::TEEN)],
      parentId: 1,
      walletStatus: WalletStatus::withoutWallet()
    );

    $this->assertEquals(2, $user->getId());
    $this->assertTrue($user->hasRole(Role::TEEN));
    $this->assertEquals(1, $user->getParentId());
  }

  // Test 3 : Vérifier la validation d'email invalide
  public function test_cannot_create_user_with_invalid_email()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Invalid email format");

    new User(
      id: 1,
      email: 'invalid-email',
      password: 'password',
      roles: [new Role(Role::PARENT)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );
  }

  // Test 4 : Vérifier qu'on ne peut pas créer un user avec un mot de passe vide
  public function test_cannot_create_user_with_empty_password()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Password cannot be empty");

    new User(
      id: 1,
      email: 'user@example.com',
      password: '',
      roles: [new Role(Role::PARENT)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );
  }

  // Test 5 : Vérifier qu'on peut ajouter un rôle
  public function test_can_add_role()
  {
    $user = new User(
      id: 1,
      email: 'user@example.com',
      password: 'password',
      roles: [new Role(Role::PARENT)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );

    $user->addRole(new Role(Role::ADMIN));

    $this->assertTrue($user->hasRole(Role::PARENT));
    $this->assertTrue($user->hasRole(Role::ADMIN));
  }

  // Test 6 : Vérifier qu'on peut supprimer un rôle
  public function test_can_remove_role()
  {
    $user = new User(
      id: 1,
      email: 'user@example.com',
      password: 'password',
      roles: [new Role(Role::PARENT), new Role(Role::ADMIN)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );

    $user->removeRole(new Role(Role::ADMIN));

    $this->assertTrue($user->hasRole(Role::PARENT));
    $this->assertFalse($user->hasRole(Role::ADMIN));
  }

  // Test 7 : Vérifier les méthodes helper isParent(), isTeen(), isAdmin()
  public function test_role_helper_methods()
  {
    $parent = new User(
      id: 1,
      email: 'parent@example.com',
      password: 'password',
      roles: [new Role(Role::PARENT)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );

    $this->assertTrue($parent->isParent());
    $this->assertFalse($parent->isTeen());
    $this->assertFalse($parent->isAdmin());
  }

  // Test 8 : Vérifier qu'on peut mettre à jour le WalletStatus
  public function test_can_update_wallet_status()
  {
    $user = new User(
      id: 1,
      email: 'user@example.com',
      password: 'password',
      roles: [new Role(Role::TEEN)],
      parentId: 1,
      walletStatus: WalletStatus::withoutWallet()
    );

    $this->assertFalse($user->hasWallet());

    $user->setWalletStatus(WalletStatus::withWallet(42));

    $this->assertTrue($user->hasWallet());
    $this->assertEquals(42, $user->getWalletId());
  }

  // Test 9 : Vérifier qu'un teen doit avoir un parentId
  public function test_teen_must_have_parent_id()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Teen must have a parent ID");

    new User(
      id: 1,
      email: 'teen@example.com',
      password: 'password',
      roles: [new Role(Role::TEEN)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );
  }

  // Test 10 : Vérifier qu'un parent ne peut pas avoir de parentId
  public function test_parent_cannot_have_parent_id()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Parent cannot have a parent ID");

    new User(
      id: 1,
      email: 'parent@example.com',
      password: 'password',
      roles: [new Role(Role::PARENT)],
      parentId: 999,
      walletStatus: WalletStatus::withoutWallet()
    );
  }

  // Test 11 : Vérifier qu'on ne peut pas créer un user sans rôles
  public function test_cannot_create_user_without_roles()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("User must have at least one role");

    new User(
      id: 1,
      email: 'user@example.com',
      password: 'password',
      roles: [],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );
  }

  // Test 12 : Vérifier getRoles() retourne un array de Role
  public function test_get_roles_returns_array_of_roles()
  {
    $user = new User(
      id: 1,
      email: 'user@example.com',
      password: 'password',
      roles: [new Role(Role::PARENT), new Role(Role::ADMIN)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );

    $roles = $user->getRoles();

    $this->assertIsArray($roles);
    $this->assertCount(2, $roles);
    $this->assertContainsOnlyInstancesOf(Role::class, $roles);
  }
}
