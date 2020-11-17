<?php

declare(strict_types=1);

namespace App\Application\Serializable\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use JsonSerializable;

class SerializableUtilisateur implements JsonSerializable
{
  private $utilisateur;
  private $complet;
  private $mdp;

  public function __construct(Utilisateur $utilisateur, bool $complet = false, string $mdp = null)
  {
    $this->utilisateur = $utilisateur;
    $this->complet = $complet;
    $this->mdp = $mdp;
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
      if (isset($this->mdp)) $data['mdp'] = $this->mdp;
      ksort($data);
    }
    return $data;
  }
}
