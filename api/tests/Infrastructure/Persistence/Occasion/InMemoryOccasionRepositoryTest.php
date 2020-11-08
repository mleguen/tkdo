<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\OccasionNotFoundException;
use App\Domain\Utilisateur\Genre;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use App\Infrastructure\Persistence\Occasion\InMemoryOccasionRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\TestCase;

class InMemoryOccasionRepositoryTest extends TestCase
{
    /** @var DoctrineUtilisateur */
    private $alice;

    /** @var DoctrineUtilisateur */
    private $bob;

    /** @var DoctrineUtilisateur */
    private $charlie;

    /** @var DoctrineOccasion */
    private $noel2019;

    /** @var DoctrineOccasion */
    private $noel2020;

    /** @var InMemoryOccasionRepository */
    private $repository;

    public function setUp(): void
    {
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice')
            ->setGenre(Genre::Feminin);
        $this->bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob')
            ->setGenre(Genre::Masculin);
        $this->charlie = (new DoctrineUtilisateur(3))
            ->setIdentifiant('charlie@tkdo.org')
            ->setNom('Charlie')
            ->setMdp('mdpcharlie')
            ->setGenre(Genre::Masculin);
        $this->noel2019 = (new DoctrineOccasion(0))
            ->setTitre("Noël 2019")
            ->setParticipants([$this->alice, $this->charlie]);
        $this->noel2020 = (new DoctrineOccasion(1))
            ->setTitre("Noël 2020")
            ->setParticipants([$this->alice, $this->bob]);
        $this->repository = new InMemoryOccasionRepository([
            0 => $this->noel2019,
            1 => $this->noel2020,
        ]);
    }

    public function testReadLastParticipeAPlusieursOccasions()
    {
        $this->assertEquals($this->noel2020, $this->repository->readLastByParticipant($this->alice->getId()));
    }

    public function testReadLastParticipeALaDerniereOccasion()
    {
        $this->assertEquals($this->noel2020, $this->repository->readLastByParticipant($this->bob->getId()));
    }

    public function testReadLastParticipeAUneAncienneOccasion()
    {
        $this->assertEquals($this->noel2019, $this->repository->readLastByParticipant($this->charlie->getId()));
    }

    public function testReadLastNeParticipeAAucuneOccasion()
    {
        $this->expectException(OccasionNotFoundException::class);
        $david = new DoctrineUtilisateur(4);
        $this->repository->readLastByParticipant($david->getId());
    }
}
