<?php

namespace App\Application\UseCase\Wallet;

use App\Application\DTO\Wallet\RecordExpenseRequest;
use App\Application\DTO\Wallet\WalletResponse;
use App\Domain\Repository\WalletRepositoryInterface;

/**
 * Use Case pour enregistrer une dépense
 */
class RecordExpenseUseCase
{
  public function __construct(
    private WalletRepositoryInterface $walletRepository
  ) {
  }

  public function execute(RecordExpenseRequest $request): WalletResponse
  {
    // 1. Récupérer le wallet
    $wallet = $this->walletRepository->findById($request->walletId);
    if ($wallet === null) {
      throw new \DomainException("Wallet not found");
    }

    // 2. Retirer l'argent (dépense)
    $wallet->withdraw($request->amount);

    // 3. Mettre à jour le wallet
    $this->walletRepository->update($wallet);

    // 4. Retourner la réponse
    return new WalletResponse(
      id: $wallet->getId(),
      userId: $wallet->getUserId(),
      balance: $wallet->getBalance(),
      weeklyAllowance: $wallet->getWeeklyAllowance(),
      lastAllowanceDate: $wallet->getLastAllowanceDate()
    );
  }
}
