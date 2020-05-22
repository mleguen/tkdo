<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\ResultatTirage;

use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use App\Infrastructure\Persistence\ResultatTirage\DoctrineResultatTirage;
use App\Infrastructure\Persistence\ResultatTirage\InMemoryResultatTirageRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\TestCase;

class InMemoryResultatTirageRepositoryTest extends TestCase
{
    /**
     * @var DoctrineOccasion
     */
    private $occasion;

    /**
     * @var DoctrineResultatTirage
     */
    private $resultatTirageAlice;

    /**
     * @var InMemoryResultatTirageRepository
     */
    private $repository;

    public function setUp()
    {
        $alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');
        $this->occasion = new DoctrineOccasion(
            0,
            "Noël 2020",
            [$alice, $bob]
        );
        $this->resultatTirageAlice = (new DoctrineResultatTirage($this->occasion, $alice))
            ->setQuiRecoit($bob);
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
        $autreOccasion = new DoctrineOccasion(
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
