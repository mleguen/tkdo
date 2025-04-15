<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeNotFoundException;
use App\Infrastructure\Persistence\Idee\InMemoryIdeeRepository;
use Tests\TestCase;

class InMemoryIdeeRepositoryTest extends TestCase
{
    public function testFindAll()
    {
        $idee = new Idee(1, 'bill.gates', 'Bill', 'Gates');

        $ideeRepository = new InMemoryIdeeRepository([1 => $idee]);

        $this->assertEquals([$idee], $ideeRepository->findAll());
    }

    public function testFindAllIdeeByDefault()
    {
        $idees = [
            1 => new Idee(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Idee(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Idee(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Idee(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Idee(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];

        $ideeRepository = new InMemoryIdeeRepository();

        $this->assertEquals(array_values($idees), $ideeRepository->findAll());
    }

    public function testFindIdeeOfId()
    {
        $idee = new Idee(1, 'bill.gates', 'Bill', 'Gates');

        $ideeRepository = new InMemoryIdeeRepository([1 => $idee]);

        $this->assertEquals($idee, $ideeRepository->findIdeeOfId(1));
    }

    public function testFindIdeeOfIdThrowsNotFoundException()
    {
        $ideeRepository = new InMemoryIdeeRepository([]);
        $this->expectException(IdeeNotFoundException::class);
        $ideeRepository->findIdeeOfId(1);
    }
}
