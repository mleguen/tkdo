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

  public function __construct(Utilisateur $utilisateur)
  {
    $this->utilisateur = $utilisateur;
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->utilisateur->getId(),
      'identifiant' => $this->utilisateur->getIdentifiant(),
      'nom' => $this->utilisateur->getNom(),
    ];
  }
}
