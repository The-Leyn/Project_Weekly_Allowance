<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Wallet;

/**
 * Interface pour la persistance des wallets
 * Contrat que l'infrastructure doit implémenter
 */
interface WalletRepositoryInterface
{
  /**
   * Sauvegarder un nouveau wallet
   */
  public function save(Wallet $wallet): void;

  /**
   * Mettre à jour un wallet existant
   */
  public function update(Wallet $wallet): void;

  /**
   * Trouver un wallet par son ID
   */
  public function findById(int $id): ?Wallet;

  /**
   * Trouver le wallet d'un utilisateur
   */
  public function findByUserId(int $userId): ?Wallet;

  /**
   * Supprimer un wallet
   */
  public function delete(int $id): void;
}
