<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

use App\Domain\Reference\Reference;

interface Utilisateur extends Reference
{
    public function getIdentifiant(): string;
    public function getNom(): string;
    
    public function setIdentifiant(string $identifiant): Utilisateur;
    public function setMdp(string $mdp): Utilisateur;
    public function setNom(string $nom): Utilisateur;
}
