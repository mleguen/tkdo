<?php

declare(strict_types=1);

namespace App\Application\Serializable\Idee;

use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Domain\Idee\Idee;
use JsonSerializable;

class SerializableIdee implements JsonSerializable
{
  /**
   * @var Idee
   */
  private $idee;

  public function __construct(Idee $idee) {
    $this->idee = $idee;
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->idee->getId(),
      'description' => $this->idee->getDescription(),
      'auteur' => new SerializableUtilisateur($this->idee->getAuteur()),
      'dateProposition' => $this->idee->getDateProposition()->format(\DateTimeInterface::ISO8601),
    ];
  }
}
