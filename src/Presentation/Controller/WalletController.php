<?php

namespace App\Presentation\Controller;

use App\Application\DTO\Wallet\CreateWalletRequest;
use App\Application\DTO\Wallet\DepositMoneyRequest;
use App\Application\DTO\Wallet\RecordExpenseRequest;
use App\Application\DTO\Wallet\SetWeeklyAllowanceRequest;
use App\Application\UseCase\Wallet\CreateWalletUseCase;
use App\Application\UseCase\Wallet\DepositMoneyUseCase;
use App\Application\UseCase\Wallet\RecordExpenseUseCase;
use App\Application\UseCase\Wallet\SetWeeklyAllowanceUseCase;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\WalletRepositoryInterface;

/**
 * Contrôleur de gestion des wallets
 */
class WalletController
{
  public function __construct(
    private CreateWalletUseCase $createWalletUseCase,
    private DepositMoneyUseCase $depositMoneyUseCase,
    private RecordExpenseUseCase $recordExpenseUseCase,
    private SetWeeklyAllowanceUseCase $setWeeklyAllowanceUseCase,
    private UserRepositoryInterface $userRepository,
    private WalletRepositoryInterface $walletRepository
  ) {
  }

  /**
   * Créer un wallet pour un utilisateur
   */
  public function create(): void
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      $request = new CreateWalletRequest(
        userId: $data['userId'] ?? 0,
        initialBalance: $data['initialBalance'] ?? 0
      );

      $response = $this->createWalletUseCase->execute($request);

      http_response_code(201);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'data' => $response->toArray()
      ]);
    } catch (\InvalidArgumentException $e) {
      http_response_code(400);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\DomainException $e) {
      http_response_code(409);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\Exception $e) {
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => 'Internal server error']);
    }
  }

  /**
   * Récupérer le wallet d'un utilisateur
   */
  public function getByUserId(int $userId): void
  {
    try {
      // Récupérer l'utilisateur pour obtenir son wallet_id
      $user = $this->userRepository->findById($userId);

      if ($user === null) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'User not found']);
        return;
      }

      // Vérifier si l'utilisateur a un wallet
      if (!$user->hasWallet()) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Wallet not found']);
        return;
      }

      // Récupérer le wallet par son ID
      $wallet = $this->walletRepository->findById($user->getWalletId());

      if ($wallet === null) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Wallet not found']);
        return;
      }

      http_response_code(200);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'data' => [
          'id' => $wallet->getId(),
          'userId' => $wallet->getUserId(),
          'balance' => $wallet->getBalance(),
          'weeklyAllowance' => $wallet->getWeeklyAllowance(),
          'lastAllowanceDate' => $wallet->getLastAllowanceDate()?->format('Y-m-d H:i:s')
        ]
      ]);
    } catch (\Exception $e) {
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => 'Internal server error']);
    }
  }

  /**
   * Déposer de l'argent
   */
  public function deposit(): void
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      $request = new DepositMoneyRequest(
        walletId: $data['walletId'] ?? 0,
        amount: $data['amount'] ?? 0
      );

      $response = $this->depositMoneyUseCase->execute($request);

      http_response_code(200);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'data' => $response->toArray()
      ]);
    } catch (\InvalidArgumentException $e) {
      http_response_code(400);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\DomainException $e) {
      http_response_code(404);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\Exception $e) {
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => 'Internal server error']);
    }
  }

  /**
   * Enregistrer une dépense
   */
  public function recordExpense(): void
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      $request = new RecordExpenseRequest(
        walletId: $data['walletId'] ?? 0,
        amount: $data['amount'] ?? 0,
        description: $data['description'] ?? ''
      );

      $response = $this->recordExpenseUseCase->execute($request);

      http_response_code(200);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'data' => $response->toArray()
      ]);
    } catch (\InvalidArgumentException $e) {
      http_response_code(400);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\DomainException $e) {
      http_response_code(400);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\Exception $e) {
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => 'Internal server error']);
    }
  }

  /**
   * Définir l'allocation hebdomadaire
   */
  public function setAllowance(): void
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      $request = new SetWeeklyAllowanceRequest(
        walletId: $data['walletId'] ?? 0,
        amount: $data['amount'] ?? 0
      );

      $response = $this->setWeeklyAllowanceUseCase->execute($request);

      http_response_code(200);
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'data' => $response->toArray()
      ]);
    } catch (\InvalidArgumentException $e) {
      http_response_code(400);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\DomainException $e) {
      http_response_code(404);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\Exception $e) {
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'error' => 'Internal server error']);
    }
  }
}
