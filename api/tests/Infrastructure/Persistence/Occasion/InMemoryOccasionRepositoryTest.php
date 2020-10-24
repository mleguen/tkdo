<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\OccasionNotFoundException;
use App\Infrastructure\Persistence\Occasion\InMemoryOccasionRepository;
use Tests\TestCase;

class InMemoryOccasionRepositoryTest extends TestCase
{
    public function testFindAll()
    {
        $occasion = new Occasion(1, 'bill.gates', 'Bill', 'Gates');

        $occasionRepository = new InMemoryOccasionRepository([1 => $occasion]);

        $this->assertEquals([$occasion], $occasionRepository->findAll());
    }

    public function testFindOccasionOfId()
    {
        $occasion = new Occasion(1, 'bill.gates', 'Bill', 'Gates');

        $occasionRepository = new InMemoryOccasionRepository([1 => $occasion]);

        $this->assertEquals($occasion, $occasionRepository->findOccasionOfId(1));
    }

    /**
     * @expectedException \App\Domain\Occasion\OccasionNotFoundException
     */
    public function testFindOccasionOfIdThrowsNotFoundException()
    {
        $occasionRepository = new InMemoryOccasionRepository([]);
        $occasionRepository->findOccasionOfId(1);
    }
}
