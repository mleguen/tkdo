<?php

declare(strict_types=1);

namespace App\Application\Serializable\Occasion;

use App\Application\Service\DateService;
use App\Domain\Occasion\Occasion;
use JsonSerializable;

class SerializableOccasion implements JsonSerializable
{
  protected $occasion;
  protected $dateService;

  public function __construct(
    Occasion $occasion,
    DateService $dateService
  )
  {
    $this->occasion = $occasion;
    $this->dateService = $dateService;
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->occasion->getId(),
      'date' => $this->dateService->encodeDate($this->occasion->getDate()),
      'titre' => $this->occasion->getTitre(),
    ];
  }
}
