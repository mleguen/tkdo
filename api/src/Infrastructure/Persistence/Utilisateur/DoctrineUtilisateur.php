<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="tkdo_utilisateur")
 */
class DoctrineUtilisateur implements Utilisateur
{

  /**
   * @var bool
   * @Column(type="boolean")
   */
  private $estAdmin = false;

  /**
   * @var string
   * @Column()
   */
  private $genre;

  /**
   * @var int
   * @Id
   * @Column(type="integer")
   * @GeneratedValue
   */
  protected $id;

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

  public function __construct(?int $id = NULL)
  {
    if (isset($id)) $this->id = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function getEstAdmin(): bool
  {
    return $this->estAdmin;
  }

  /**
   * {@inheritdoc}
   */
  public function getGenre(): string
  {
    return $this->genre;
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
  public function setEstAdmin(bool $estAdmin): Utilisateur
  {
    $this->estAdmin = $estAdmin;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setGenre(string $genre): Utilisateur
  {
    $this->genre = $genre;
    return $this;
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