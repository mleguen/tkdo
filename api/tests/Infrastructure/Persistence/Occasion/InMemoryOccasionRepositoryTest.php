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

    public function testFindAllOccasionByDefault()
    {
        $occasions = [
            1 => new Occasion(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Occasion(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Occasion(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Occasion(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Occasion(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];

        $occasionRepository = new InMemoryOccasionRepository();

        $this->assertEquals(array_values($occasions), $occasionRepository->findAll());
    }

    public function testFindOccasionOfId()
    {
        $occasion = new Occasion(1, 'bill.gates', 'Bill', 'Gates');

        $occasionRepository = new InMemoryOccasionRepository([1 => $occasion]);

        $this->assertEquals($occasion, $occasionRepository->findOccasionOfId(1));
    }

    public function testFindOccasionOfIdThrowsNotFoundException()
    {
        $occasionRepository = new InMemoryOccasionRepository([]);
        $this->expectException(OccasionNotFoundException::class);
        $occasionRepository->findOccasionOfId(1);
    }
}
