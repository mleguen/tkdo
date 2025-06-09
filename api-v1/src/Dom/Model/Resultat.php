<?php
declare(strict_types=1);

namespace App\Dom\Model;

interface Resultat
{
    public function getQuiOffre(): Utilisateur;
    public function getQuiRecoit(): Utilisateur;
    public function setOccasion(Occasion $occasion): Resultat;
    public function setQuiOffre(Utilisateur $quiOffre): Resultat;
    public function setQuiRecoit(Utilisateur $quiRecoit): Resultat;
}
