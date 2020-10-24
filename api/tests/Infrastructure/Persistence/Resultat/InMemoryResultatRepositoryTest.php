<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Resultat;

use App\Domain\Resultat\Resultat;
use App\Domain\Resultat\ResultatNotFoundException;
use App\Infrastructure\Persistence\Resultat\InMemoryResultatRepository;
use Tests\TestCase;

class InMemoryResultatRepositoryTest extends TestCase
{
    public function testFindAll()
    {
        $resultat = new Resultat(1, 'bill.gates', 'Bill', 'Gates');

        $resultatRepository = new InMemoryResultatRepository([1 => $resultat]);

        $this->assertEquals([$resultat], $resultatRepository->findAll());
    }

    public function testFindResultatOfId()
    {
        $resultat = new Resultat(1, 'bill.gates', 'Bill', 'Gates');

        $resultatRepository = new InMemoryResultatRepository([1 => $resultat]);

        $this->assertEquals($resultat, $resultatRepository->findResultatOfId(1));
    }

    /**
     * @expectedException \App\Domain\Resultat\ResultatNotFoundException
     */
    public function testFindResultatOfIdThrowsNotFoundException()
    {
        $resultatRepository = new InMemoryResultatRepository([]);
        $resultatRepository->findResultatOfId(1);
    }
}
