<?php

declare(strict_types=1);

namespace App\Application\Serializable\Occasion;

use App\Domain\Occasion\Occasion;
use JsonSerializable;

class SerializableOccasion implements JsonSerializable
{
  protected $occasion;

  public function __construct(Occasion $occasion)
  {
    $this->occasion = $occasion;
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->occasion->getId(),
      'titre' => $this->occasion->getTitre(),
    ];
  }
}
