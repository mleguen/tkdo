<?php

declare(strict_types=1);

namespace App\Application\Serializable\Occasion;

use App\Application\Serializable\ResultatTirage\SerializableResultatTirage;
use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Domain\Occasion\Occasion;
use App\Domain\ResultatTirage\ResultatTirage;
use App\Domain\Utilisateur\Utilisateur;
use JsonSerializable;

class SerializableOccasion implements JsonSerializable
{
  /**
   * @var Occasion
   */
  private $occasion;

  /**
   * @var ResultatTirage[]
   */
  private $resultatsTirage;

  /**
   * @param ResultatTirage $resultatsTirage
   */
  public function __construct(Occasion $occasion, array $resultatsTirage)
  {
    $this->occasion = $occasion;
    $this->resultatsTirage = $resultatsTirage;
  }

  public function jsonSerialize(): array
  {
    return [
      'id' => $this->occasion->getId(),
      'titre' => $this->occasion->getTitre(),
      'participants' => array_map(
        function (Utilisateur $u) {
          return new SerializableUtilisateur($u);
        },
        $this->occasion->getParticipants()
      ),
      'resultatsTirage' => array_map(
        function (ResultatTirage $rt) {
          return new SerializableResultatTirage($rt);
        },
        $this->resultatsTirage
      )
    ];
  }
}
