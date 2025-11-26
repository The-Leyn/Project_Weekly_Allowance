<?php

namespace App\Domain\ValueObject;

/**
 * Value Object représentant un rôle utilisateur
 * Immutable et toujours valide
 */
class Role
{
  public const PARENT = 'PARENT';
  public const TEEN = 'TEEN';
  public const ADMIN = 'ADMIN';

  private const VALID_ROLES = [
    self::PARENT,
    self::TEEN,
    self::ADMIN,
  ];

  private string $value;

  public function __construct(string $value)
  {
    if (!in_array($value, self::VALID_ROLES, true)) {
      throw new \InvalidArgumentException("Invalid role: {$value}");
    }

    $this->value = $value;
  }

  public function getValue(): string
  {
    return $this->value;
  }

  public function equals(Role $other): bool
  {
    return $this->value === $other->value;
  }

  public function isParent(): bool
  {
    return $this->value === self::PARENT;
  }

  public function isTeen(): bool
  {
    return $this->value === self::TEEN;
  }

  public function isAdmin(): bool
  {
    return $this->value === self::ADMIN;
  }

  public function __toString(): string
  {
    return $this->value;
  }
}
