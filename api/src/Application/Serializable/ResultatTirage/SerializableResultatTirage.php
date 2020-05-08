<?php

declare(strict_types=1);

namespace App\Application\Serializable\ResultatTirage;

use App\Domain\ResultatTirage\ResultatTirage;
use JsonSerializable;

class SerializableResultatTirage implements JsonSerializable
{
  /**
   * @var ResultatTirage
   */
  private $resultatTirage;

  public function __construct(ResultatTirage $resultatTirage)
  {
    $this->resultatTirage = $resultatTirage;
  }

  public function jsonSerialize(): array
  {
    return [
      "idQuiOffre" => $this->resultatTirage->getQuiOffre()->getId(),
      "idQuiRecoit" => $this->resultatTirage->getQuiRecoit()->getId(),
    ];
  }
}
