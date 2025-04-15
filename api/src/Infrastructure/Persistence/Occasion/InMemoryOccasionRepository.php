<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\OccasionNotFoundException;
use App\Domain\Occasion\OccasionRepository;

class InMemoryOccasionRepository implements OccasionRepository
{
    /**
     * @var Occasion[]
     */
    private array $occasions;

    /**
     * @param Occasion[]|null $occasions
     */
    public function __construct(array $occasions = null)
    {
        $this->occasions = $occasions ?? [
            1 => new Occasion(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Occasion(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Occasion(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Occasion(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Occasion(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->occasions);
    }

    /**
     * {@inheritdoc}
     */
    public function findOccasionOfId(int $id): Occasion
    {
        if (!isset($this->occasions[$id])) {
            throw new OccasionNotFoundException();
        }

        return $this->occasions[$id];
    }
}
