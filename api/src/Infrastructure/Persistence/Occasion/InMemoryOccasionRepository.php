<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\OccasionNotFoundException;
use App\Domain\Occasion\OccasionRepository;

class InMemoryOccasionRepository implements OccasionRepository
{
    /**
     * @var DoctrineOccasion[]
     */
    private $occasions;

    /**
     * @param DoctrineOccasion[] $occasions
     */
    public function __construct(array $occasions = [])
    {
        $this->occasions = $occasions;
    }

    /**
     * {@inheritdoc}
     */
    public function readLast(): Occasion
    {
        if (empty($this->occasions)) throw new OccasionNotFoundException();
        $lastId = max(array_keys($this->occasions));
        return clone $this->occasions[$lastId];
    }
}
