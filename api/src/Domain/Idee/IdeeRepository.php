<?php
declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\Utilisateur\Utilisateur;

interface IdeeRepository
{
    // /**
    //  * @return Idee[]
    //  */
    // public function findAll(): array;

    /**
     * @param string $idUtilisateur
     * @return Idee[]
     */
    public function findAllByUtilisateur(Utilisateur $utilisateur): array;

    /**
     * @param int $id
     * @return Idee
     * @throws IdeeInconnueException
     */
    public function find(int $id): Idee;

    /**
     * @param Idee $idee
     * @return Idee
     * @throws IdeeInconnueException
     */
    public function persist(Idee $idee): Idee;

    /**
     * @param Idee $idee
     * @return Idee
     * @throws IdeeInconnueException
     */
    public function remove(Idee $idee);
}
