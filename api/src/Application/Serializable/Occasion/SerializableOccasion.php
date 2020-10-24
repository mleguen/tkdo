<?php

declare(strict_types=1);

namespace App\Application\Serializable\Occasion;

use App\Application\Serializable\Resultat\SerializableResultat;
use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Domain\Occasion\Occasion;
use App\Domain\Resultat\Resultat;
use App\Domain\Utilisateur\Utilisateur;
use JsonSerializable;

class SerializableOccasion implements JsonSerializable
{
  /**
   * @var Occasion
   */
  private $occasion;

  /**
   * @var Resultat[]
   */
  private $resultats;

  /**
   * @param Resultat $resultats
   */
  public function __construct(Occasion $occasion, array $resultats)
  {
    $this->occasion = $occasion;
    $this->resultats = $resultats;
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
      'resultats' => array_map(
        function (Resultat $rt) {
          return new SerializableResultat($rt);
        },
        $this->resultats
      )
    ];
  }
}
