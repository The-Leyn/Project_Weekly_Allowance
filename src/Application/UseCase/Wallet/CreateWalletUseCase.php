<?php

namespace App\Application\UseCase\Wallet;

use App\Application\DTO\Wallet\CreateWalletRequest;
use App\Application\DTO\Wallet\WalletResponse;
use App\Domain\Entity\Wallet;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\WalletRepositoryInterface;
use App\Domain\ValueObject\WalletStatus;

/**
 * Use Case pour créer un wallet
 */
class CreateWalletUseCase
{
  public function __construct(
    private UserRepositoryInterface $userRepository,
    private WalletRepositoryInterface $walletRepository
  ) {
  }

  public function execute(CreateWalletRequest $request): WalletResponse
  {
    // 1. Vérifier que l'utilisateur existe
    $user = $this->userRepository->findById($request->userId);
    if ($user === null) {
      throw new \DomainException("User not found");
    }

    // 2. Vérifier qu'il n'a pas déjà un wallet
    if ($user->hasWallet()) {
      throw new \DomainException("User already has a wallet");
    }

    // 3. Créer le wallet
    $wallet = new Wallet(
      id: 0, // Sera défini par le repository
      userId: $request->userId,
      balance: $request->initialBalance
    );

    // 4. Sauvegarder le wallet
    $this->walletRepository->save($wallet);

    // 5. Mettre à jour le WalletStatus de l'utilisateur
    $user->setWalletStatus(WalletStatus::withWallet($wallet->getId()));
    $this->userRepository->update($user);

    // 6. Retourner la réponse
    return new WalletResponse(
      id: $wallet->getId(),
      userId: $wallet->getUserId(),
      balance: $wallet->getBalance(),
      weeklyAllowance: $wallet->getWeeklyAllowance(),
      lastAllowanceDate: $wallet->getLastAllowanceDate()
    );
  }
}
