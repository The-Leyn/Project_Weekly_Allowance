<?php

namespace Tests\Unit\UseCase\Auth;

use App\Application\DTO\Auth\RegisterUserRequest;
use App\Application\DTO\Auth\AuthResponse;
use App\Application\UseCase\Auth\RegisterUserUseCase;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\WalletRepositoryInterface;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\WalletStatus;
use PHPUnit\Framework\TestCase;

class RegisterUserUseCaseTest extends TestCase
{
  private UserRepositoryInterface $userRepository;
  private WalletRepositoryInterface $walletRepository;
  private RegisterUserUseCase $useCase;

  protected function setUp(): void
  {
    // Créer des mocks des repositories
    $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    $this->walletRepository = $this->createMock(WalletRepositoryInterface::class);
    $this->useCase = new RegisterUserUseCase($this->userRepository, $this->walletRepository);
  }

  // Test 1 : Vérifier qu'on peut inscrire un parent
  public function test_can_register_parent()
  {
    $request = new RegisterUserRequest(
      email: 'parent@example.com',
      password: 'password123',
      role: Role::PARENT
    );

    // Mock : vérifier que l'email n'existe pas
    $this->userRepository
      ->expects($this->once())
      ->method('findByEmail')
      ->with('parent@example.com')
      ->willReturn(null);

    // Mock : vérifier que save est appelé
    $this->userRepository
      ->expects($this->once())
      ->method('save')
      ->with($this->callback(function (User $user) {
        return $user->getEmail() === 'parent@example.com'
          && $user->hasRole(Role::PARENT)
          && $user->getParentId() === null
          && !$user->hasWallet();
      }));

    $response = $this->useCase->execute($request);

    $this->assertInstanceOf(AuthResponse::class, $response);
    $this->assertEquals('parent@example.com', $response->email);
    $this->assertContains(Role::PARENT, $response->roles);
    $this->assertFalse($response->hasWallet);
    $this->assertNotEmpty($response->token);
  }

  // Test 2 : Vérifier qu'on peut inscrire un teen avec parentId et wallet partagé
  public function test_can_register_teen_with_parent_wallet()
  {
    $request = new RegisterUserRequest(
      email: 'teen@example.com',
      password: 'password123',
      role: Role::TEEN,
      parentId: 1
    );

    // Mock : vérifier que l'email n'existe pas
    $this->userRepository
      ->expects($this->once())
      ->method('findByEmail')
      ->with('teen@example.com')
      ->willReturn(null);

    // Mock : vérifier que le parent existe (avec un wallet)
    $parent = new User(
      id: 1,
      email: 'parent@example.com',
      password: 'hashed',
      roles: [new Role(Role::PARENT)],
      parentId: null,
      walletStatus: WalletStatus::withWallet(5) // Parent a le wallet_id = 5
    );

    $this->userRepository
      ->expects($this->exactly(2)) // Appelé 2 fois : validation + récupération wallet
      ->method('findById')
      ->with(1)
      ->willReturn($parent);

    // Mock : vérifier que save est appelé
    $this->userRepository
      ->expects($this->once())
      ->method('save');

    // Mock : vérifier que update est appelé pour mettre à jour le wallet_id du teen
    $this->userRepository
      ->expects($this->once())
      ->method('update')
      ->with($this->callback(function (User $user) {
        return $user->getEmail() === 'teen@example.com'
          && $user->hasWallet()
          && $user->getWalletId() === 5; // Teen hérite du wallet_id du parent
      }));

    $response = $this->useCase->execute($request);

    $this->assertInstanceOf(AuthResponse::class, $response);
    $this->assertEquals('teen@example.com', $response->email);
    $this->assertContains(Role::TEEN, $response->roles);
    $this->assertTrue($response->hasWallet); // Teen a maintenant un wallet (partagé)
  }

  // Test 3 : Vérifier qu'on ne peut pas inscrire un utilisateur avec un email existant
  public function test_cannot_register_user_with_existing_email()
  {
    $request = new RegisterUserRequest(
      email: 'existing@example.com',
      password: 'password123',
      role: Role::PARENT
    );

    // Mock : l'email existe déjà
    $existingUser = new User(
      id: 1,
      email: 'existing@example.com',
      password: 'hashed',
      roles: [new Role(Role::PARENT)],
      parentId: null,
      walletStatus: WalletStatus::withoutWallet()
    );

    $this->userRepository
      ->expects($this->once())
      ->method('findByEmail')
      ->with('existing@example.com')
      ->willReturn($existingUser);

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage("Email already exists");

    $this->useCase->execute($request);
  }

  // Test 4 : Vérifier qu'un teen doit avoir un parent existant
  public function test_teen_must_have_existing_parent()
  {
    $request = new RegisterUserRequest(
      email: 'teen@example.com',
      password: 'password123',
      role: Role::TEEN,
      parentId: 999
    );

    // Mock : l'email n'existe pas
    $this->userRepository
      ->expects($this->once())
      ->method('findByEmail')
      ->willReturn(null);

    // Mock : le parent n'existe pas
    $this->userRepository
      ->expects($this->once())
      ->method('findById')
      ->with(999)
      ->willReturn(null);

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage("Parent not found");

    $this->useCase->execute($request);
  }

  // Test 5 : Vérifier que le mot de passe est hashé
  public function test_password_is_hashed()
  {
    $request = new RegisterUserRequest(
      email: 'user@example.com',
      password: 'plaintext',
      role: Role::PARENT
    );

    $this->userRepository
      ->method('findByEmail')
      ->willReturn(null);

    $this->userRepository
      ->expects($this->once())
      ->method('save')
      ->with($this->callback(function (User $user) {
        // Vérifier que le mot de passe n'est pas en clair
        return $user->getPassword() !== 'plaintext'
          && password_verify('plaintext', $user->getPassword());
      }));

    $this->useCase->execute($request);
  }

  // Test 6 : Vérifier qu'un rôle invalide est rejeté
  public function test_invalid_role_is_rejected()
  {
    $request = new RegisterUserRequest(
      email: 'user@example.com',
      password: 'password123',
      role: 'INVALID_ROLE'
    );

    $this->userRepository
      ->method('findByEmail')
      ->willReturn(null);

    $this->expectException(\InvalidArgumentException::class);

    $this->useCase->execute($request);
  }
}
