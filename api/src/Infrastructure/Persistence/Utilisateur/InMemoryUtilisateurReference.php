<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\ReferenceException;
use App\Domain\Utilisateur\Utilisateur;

class InMemoryUtilisateurReference implements Utilisateur
{
  /**
   * @var int
   */
  protected $id;

  public function __construct(int $id)
  {
    $this->id = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function getId(): int
  {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentifiant(): string
  {
    throw new ReferenceException();
  }

  /**
   * {@inheritdoc}
   */
  public function getNom(): string
  {
    throw new ReferenceException();
  }

  /**
   * {@inheritdoc}
   */
  public function setIdentifiant(string $identifiant): Utilisateur
  {
    throw new ReferenceException();
  }

  /**
   * {@inheritdoc}
   */
  public function setMdp(string $mdp): Utilisateur
  {
    throw new ReferenceException();
  }

  /**
   * {@inheritdoc}
   */
  public function setNom(string $nom): Utilisateur
  {
    throw new ReferenceException();
  }
}