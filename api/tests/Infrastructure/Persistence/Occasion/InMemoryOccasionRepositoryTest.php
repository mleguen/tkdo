<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\OccasionNotFoundException;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use App\Infrastructure\Persistence\Occasion\InMemoryOccasionRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\TestCase;

class InMemoryOccasionRepositoryTest extends TestCase
{
    public function testReadLast()
    {
        $alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');
        $noel2019 = new DoctrineOccasion(
            0,
            "Noël 2019",
            [$alice, $bob]
        );
        $noel2020 = new DoctrineOccasion(
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

    public function testReadLastAucuneOccasion()
    {
        $this->expectException(OccasionNotFoundException::class);
        $repository = new InMemoryOccasionRepository([]);
        $repository->readLast();
    }
}
