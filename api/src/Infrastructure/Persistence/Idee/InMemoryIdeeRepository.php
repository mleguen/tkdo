<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeNotFoundException;
use App\Domain\Idee\IdeeRepository;

class InMemoryIdeeRepository implements IdeeRepository
{
    /**
     * @var Idee[]
     */
    private array $idees;

    /**
     * @param Idee[]|null $idees
     */
    public function __construct(?array $idees = null)
    {
        $this->idees = $idees ?? [
            1 => new Idee(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Idee(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Idee(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Idee(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Idee(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->idees);
    }

    /**
     * {@inheritdoc}
     */
    public function findIdeeOfId(int $id): Idee
    {
        if (!isset($this->idees[$id])) {
            throw new IdeeNotFoundException();
        }

        return $this->idees[$id];
    }
}
