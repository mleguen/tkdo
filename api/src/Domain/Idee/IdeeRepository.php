<?php
declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\Utilisateur\Utilisateur;

interface IdeeRepository
{
    public function create(
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        \DateTime $dateProposition
    ): Idee;

    /**
     * @throws IdeeInconnueException
     */
    public function read(int $id, bool $reference = false): Idee;

    /**
     * @return Idee[]
     */
    public function readByUtilisateur(Utilisateur $utilisateur): array;

    /**
     * @throws IdeeInconnueException
     */
    public function delete(Idee $idee);
}
