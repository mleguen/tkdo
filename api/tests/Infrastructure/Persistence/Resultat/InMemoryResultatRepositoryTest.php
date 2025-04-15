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

    public function testFindAllResultatByDefault()
    {
        $resultats = [
            1 => new Resultat(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Resultat(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Resultat(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Resultat(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Resultat(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];

        $resultatRepository = new InMemoryResultatRepository();

        $this->assertEquals(array_values($resultats), $resultatRepository->findAll());
    }

    public function testFindResultatOfId()
    {
        $resultat = new Resultat(1, 'bill.gates', 'Bill', 'Gates');

        $resultatRepository = new InMemoryResultatRepository([1 => $resultat]);

        $this->assertEquals($resultat, $resultatRepository->findResultatOfId(1));
    }

    public function testFindResultatOfIdThrowsNotFoundException()
    {
        $resultatRepository = new InMemoryResultatRepository([]);
        $this->expectException(ResultatNotFoundException::class);
        $resultatRepository->findResultatOfId(1);
    }
}
