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

    public function testFindIdeeOfId()
    {
        $idee = new Idee(1, 'bill.gates', 'Bill', 'Gates');

        $ideeRepository = new InMemoryIdeeRepository([1 => $idee]);

        $this->assertEquals($idee, $ideeRepository->findIdeeOfId(1));
    }

    /**
     * @expectedException \App\Domain\Idee\IdeeNotFoundException
     */
    public function testFindIdeeOfIdThrowsNotFoundException()
    {
        $ideeRepository = new InMemoryIdeeRepository([]);
        $ideeRepository->findIdeeOfId(1);
    }
}
