<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Occasion;

use App\Infrastructure\Persistence\Occasion\InMemoryOccasion;
use App\Infrastructure\Persistence\Occasion\InMemoryOccasionRepository;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use Tests\TestCase;

class InMemoryOccasionRepositoryTest extends TestCase
{
    public function testReadLast()
    {
        $alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'mdpalice', 'Alice');
        $bob = new InMemoryUtilisateur(1, 'bob@tkdo.org', 'Bob', 'Bob');
        $noel2019 = new InMemoryOccasion(
            0,
            "Noël 2019",
            [$alice, $bob]
        );
        $noel2020 = new InMemoryOccasion(
            1,
            "Noël 2020",
            [$alice, $bob]
        );
        $repository = new InMemoryOccasionRepository([
            0 => $noel2019,
            1 => $noel2020,
        ]);
        $this->assertEquals($noel2020, $repository->readLast());
    }

    /**
     * @expectedException \App\Domain\Occasion\AucuneOccasionException
     */
    public function testReadLastAucuneOccasion()
    {
        $repository = new InMemoryOccasionRepository([]);
        $repository->readLast();
    }
}
