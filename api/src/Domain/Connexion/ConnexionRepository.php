<?php

declare(strict_types=1);

namespace App\Domain\Connexion;

interface ConnexionRepository
{
    /**
     * @return Connexion[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return Connexion
     * @throws ConnexionNotFoundException
     */
    public function findConnexionOfId(int $id): Connexion;
}
