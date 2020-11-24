<?php

declare(strict_types=1);

namespace App\Application\Serializable\Occasion;

use App\Application\Serializable\Resultat\SerializableResultat;
use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Domain\Occasion\Occasion;
use App\Domain\Resultat\Resultat;
use App\Domain\Utilisateur\Utilisateur;

class SerializableOccasionDetaillee extends SerializableOccasion
{
  private $resultats;

  /**
   * @param Resultat[] $resultats
   */
  public function __construct(Occasion $occasion, array $resultats)
  {
    parent::__construct($occasion);
    $this->resultats = $resultats;
  }

  public function jsonSerialize(): array
  {
    return array_merge(parent::jsonSerialize(), [
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
      ),
    ]);
  }
}
