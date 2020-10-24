<?php

declare(strict_types=1);

namespace App\Application\Serializable\Resultat;

use App\Domain\Resultat\Resultat;
use JsonSerializable;

class SerializableResultat implements JsonSerializable
{
  /**
   * @var Resultat
   */
  private $resultat;

  public function __construct(Resultat $resultat)
  {
    $this->resultat = $resultat;
  }

  public function jsonSerialize(): array
  {
    return [
      "idQuiOffre" => $this->resultat->getQuiOffre()->getId(),
      "idQuiRecoit" => $this->resultat->getQuiRecoit()->getId(),
    ];
  }
}
