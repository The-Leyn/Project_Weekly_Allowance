<?php

namespace Tests\Unit\ValueObject;

use App\Domain\ValueObject\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
  // Test 1 : Vérifier qu'on peut créer un rôle PARENT
  public function test_can_create_parent_role()
  {
    $role = new Role(Role::PARENT);

    $this->assertEquals(Role::PARENT, $role->getValue());
  }

  // Test 2 : Vérifier qu'on peut créer un rôle TEEN
  public function test_can_create_teen_role()
  {
    $role = new Role(Role::TEEN);

    $this->assertEquals(Role::TEEN, $role->getValue());
  }

  // Test 3 : Vérifier qu'on peut créer un rôle ADMIN
  public function test_can_create_admin_role()
  {
    $role = new Role(Role::ADMIN);

    $this->assertEquals(Role::ADMIN, $role->getValue());
  }

  // Test 4 : Vérifier qu'on ne peut pas créer un rôle invalide
  public function test_cannot_create_invalid_role()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("Invalid role: INVALID_ROLE");

    new Role('INVALID_ROLE');
  }

  // Test 5 : Vérifier la méthode equals()
  public function test_equals_method_returns_true_for_same_role()
  {
    $role1 = new Role(Role::PARENT);
    $role2 = new Role(Role::PARENT);

    $this->assertTrue($role1->equals($role2));
  }

  // Test 6 : Vérifier la méthode equals() retourne false pour des rôles différents
  public function test_equals_method_returns_false_for_different_roles()
  {
    $role1 = new Role(Role::PARENT);
    $role2 = new Role(Role::TEEN);

    $this->assertFalse($role1->equals($role2));
  }

  // Test 7 : Vérifier l'immutabilité (pas de setter)
  public function test_role_is_immutable()
  {
    $role = new Role(Role::PARENT);

    // Vérifier qu'il n'y a pas de méthode setValue
    $this->assertFalse(method_exists($role, 'setValue'));
  }

  // Test 8 : Vérifier la méthode isParent()
  public function test_is_parent_returns_true_for_parent_role()
  {
    $role = new Role(Role::PARENT);

    $this->assertTrue($role->isParent());
    $this->assertFalse($role->isTeen());
    $this->assertFalse($role->isAdmin());
  }

  // Test 9 : Vérifier la méthode isTeen()
  public function test_is_teen_returns_true_for_teen_role()
  {
    $role = new Role(Role::TEEN);

    $this->assertTrue($role->isTeen());
    $this->assertFalse($role->isParent());
    $this->assertFalse($role->isAdmin());
  }

  // Test 10 : Vérifier la méthode isAdmin()
  public function test_is_admin_returns_true_for_admin_role()
  {
    $role = new Role(Role::ADMIN);

    $this->assertTrue($role->isAdmin());
    $this->assertFalse($role->isParent());
    $this->assertFalse($role->isTeen());
  }
}
