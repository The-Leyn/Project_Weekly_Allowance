<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;

/**
 * Interface pour la persistance des utilisateurs
 * Contrat que l'infrastructure doit implémenter
 */
interface UserRepositoryInterface
{
  /**
   * Sauvegarder un nouvel utilisateur
   */
  public function save(User $user): void;

  /**
   * Mettre à jour un utilisateur existant
   */
  public function update(User $user): void;

  /**
   * Trouver un utilisateur par son ID
   */
  public function findById(int $id): ?User;

  /**
   * Trouver un utilisateur par son email
   */
  public function findByEmail(string $email): ?User;

  /**
   * Trouver tous les utilisateurs ayant un rôle spécifique
   * @return User[]
   */
  public function findUsersByRole(string $role): array;

  /**
   * Trouver tous les utilisateurs (teens) d'un parent
   * @return User[]
   */
  public function findUsersByParentId(int $parentId): array;

  /**
   * Supprimer un utilisateur
   */
  public function delete(int $id): void;
}
