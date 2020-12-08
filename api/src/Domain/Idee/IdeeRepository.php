<?php
declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\Utilisateur\Utilisateur;
use DateTime;

interface IdeeRepository
{
    public function create(
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        DateTime $dateProposition
    ): Idee;

    /**
     * @throws IdeeNotFoundException
     */
    public function read(int $id, bool $reference = false): Idee;

    /**
     * @return Idee[]
     */
    public function readAllByNotifPeriodique(Utilisateur $utilisateur): array;

    /**
     * @return Idee[]
     */
    public function readAllByUtilisateur(Utilisateur $utilisateur, bool $supprimee = null): array;
    
    public function update(Idee $idee): Idee;
}
