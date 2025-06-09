<?php
declare(strict_types=1);

namespace App\Dom\Model;

interface Exclusion
{
    public function getQuiOffre(): Utilisateur;
    public function getQuiNeDoitPasRecevoir(): Utilisateur;
    public function setQuiOffre(Utilisateur $quiOffre): Exclusion;
    public function setQuiNeDoitPasRecevoir(Utilisateur $quiNeDoitPasRecevoir): Exclusion;
}
