<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Reference\DoctrineReference;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_utilisateur")
 */
class DoctrineUtilisateur extends DoctrineReference implements Utilisateur
{
  /**
   * @var string
   * @Column()
   */
  private $identifiant;

  /**
   * @var string
   * @Column()
   */
  private $mdp;

  /**
   * @var string
   * @Column()
   */
  private $nom;

  /**
   * {@inheritdoc}
   */
  public function getIdentifiant(): string
  {
    return $this->identifiant;
  }

  public function getMdp(): string
  {
    return $this->mdp;
  }

  /**
   * {@inheritdoc}
   */
  public function getNom(): string
  {
    return $this->nom;
  }

  /**
   * {@inheritdoc}
   */
  public function setIdentifiant(string $identifiant): Utilisateur
  {
    $this->identifiant = $identifiant;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMdp(string $mdp): Utilisateur
  {
    $this->mdp = $mdp;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setNom(string $nom): Utilisateur
  {
    $this->nom = $nom;
    return $this;
  }
}
