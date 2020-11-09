<?php

declare(strict_types=1);

namespace App\Application\Serializable\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use JsonSerializable;

class SerializableUtilisateur implements JsonSerializable
{
  /**
   * @var Utilisateur
   */
  private $utilisateur;

  /**
   * @var bool
   */
  private $complet;

  public function __construct(Utilisateur $utilisateur, bool $complet = false)
  {
    $this->utilisateur = $utilisateur;
    $this->complet = $complet;
  }

  public function jsonSerialize(): array
  {
    $data = [
      'genre' => $this->utilisateur->getGenre(),
      'id' => $this->utilisateur->getId(),
      'nom' => $this->utilisateur->getNom(),
    ];
    if ($this->complet) {
      $data = array_merge($data, [
        'estAdmin' => $this->utilisateur->getEstAdmin(),
        'identifiant' => $this->utilisateur->getIdentifiant(),
      ]);
      ksort($data);
    }
    return $data;
  }
}
