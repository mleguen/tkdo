<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Model\Occasion;
use App\Dom\Model\Resultat;
use App\Dom\Model\Utilisateur;

interface ResultatRepository
{
    public function create(Occasion $occasion, Utilisateur $quiOffre, Utilisateur $quiRecoit): Resultat;

    public function deleteByOccasion(Occasion $occasion);
    
    /**
     * Indique s'il y a des résultats pour l'occasion donnée
     * (si le tirage a déjà été fait ou partiellement fait)
     */
    public function hasForOccasion(Occasion $occasion): bool;

    /** @return Resultat[] */
    public function readByOccasion(Occasion $occasion): array;

    /** @return Resultat[] */
    public function readByParticipantsOccasion(Occasion $occasion): array;
}
