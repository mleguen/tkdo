<?php

declare(strict_types=1);

namespace App\Application\Serializable\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use JsonSerializable;

class SerializableUtilisateur implements JsonSerializable
{
  private $utilisateur;
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
        'email' => $this->utilisateur->getEmail(),
        'estAdmin' => $this->utilisateur->getEstAdmin(),
        'identifiant' => $this->utilisateur->getIdentifiant(),
        'prefNotifIdees' => $this->utilisateur->getPrefNotifIdees()
      ]);

      ksort($data);
    }
    return $data;
  }
}
