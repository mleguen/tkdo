<?php
declare(strict_types=1);

namespace App\Domain\Idee;

interface IdeeRepository
{
    /**
     * @return Idee[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return Idee
     * @throws IdeeNotFoundException
     */
    public function findIdeeOfId(int $id): Idee;
}
