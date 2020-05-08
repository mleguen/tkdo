<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\ResultatTirage;

use App\Infrastructure\Persistence\Occasion\InMemoryOccasion;
use App\Infrastructure\Persistence\ResultatTirage\InMemoryResultatTirage;
use App\Infrastructure\Persistence\ResultatTirage\InMemoryResultatTirageRepository;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use Tests\TestCase;

class InMemoryResultatTirageRepositoryTest extends TestCase
{
    /**
     * @var InMemoryOccasion
     */
    private $occasion;

    /**
     * @var InMemoryResultatTirage
     */
    private $resultatTirageAlice;

    /**
     * @var InMemoryResultatTirageRepository
     */
    private $repository;

    public function setUp()
    {
        $alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'mdpalice', 'Alice');
        $bob = new InMemoryUtilisateur(1, 'bob@tkdo.org', 'Bob', 'Bob');
        $this->occasion = new InMemoryOccasion(
            0,
            "Noël 2020",
            [$alice, $bob]
        );
        $this->resultatTirageAlice = new InMemoryResultatTirage($this->occasion, $alice, $bob);
        $this->repository = new InMemoryResultatTirageRepository([
            0 => $this->resultatTirageAlice,
        ]);
    }

    public function testReadByOccasion()
    {
        $this->assertEquals(
            [$this->resultatTirageAlice],
            $this->repository->readByOccasion($this->occasion)
        );
    }

    public function testReadByOccasionAucunResultat()
    {
        $autreOccasion = new InMemoryOccasion(
            1,
            "Noël 2021",
            []
        );
        $this->assertEquals(
            [],
            $this->repository->readByOccasion($autreOccasion)
        );
    }
}
