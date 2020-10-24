<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Resultat;

use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use App\Infrastructure\Persistence\Resultat\DoctrineResultat;
use App\Infrastructure\Persistence\Resultat\InMemoryResultatRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\TestCase;

class InMemoryResultatRepositoryTest extends TestCase
{
    /**
     * @var DoctrineOccasion
     */
    private $occasion;

    /**
     * @var DoctrineResultat
     */
    private $resultatAlice;

    /**
     * @var InMemoryResultatRepository
     */
    private $repository;

    public function setUp(): void
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
        $this->resultatAlice = (new DoctrineResultat($this->occasion, $alice))
            ->setQuiRecoit($bob);
        $this->repository = new InMemoryResultatRepository([
            0 => $this->resultatAlice,
        ]);
    }

    public function testReadByOccasion()
    {
        $this->assertEquals(
            [$this->resultatAlice],
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
