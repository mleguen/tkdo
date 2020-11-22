<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurRepository;
use Tests\TestCase;

class InMemoryUtilisateurRepositoryTest extends TestCase
{
    /**
     * @var DoctrineUtilisateur
     */
    private $alice;

    /**
     * @var DoctrineUtilisateur
     */
    private $inconnu;

    /**
     * @var InMemoryUtilisateurRepository
     */
    private $repository;

    public function setUp(): void
    {
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $this->inconnu = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');
        $this->repository = new InMemoryUtilisateurRepository([$this->alice->getId() => $this->alice]);
    }
    
    public function testRead()
    {
        $this->assertEquals($this->alice, $this->repository->read($this->alice->getId()));
    }

    public function testReadUtilisateurInconnu()
    {
        $this->expectException(UtilisateurNotFoundException::class);
        $this->repository->read($this->inconnu->getId());
    }

    public function testReadOneByIdentifiant()
    {
        $this->assertEquals($this->alice, $this->repository->readOneByIdentifiant('alice@tkdo.org'));
    }

    public function testReadOneByIdentifiantMauvaisIdentifiant()
    {
        $this->expectException(UtilisateurNotFoundException::class);
        $this->repository->readOneByIdentifiant('mauvaisIdentifiant');
    }

    public function testUpdate()
    {
        $aliceModifiee = (clone $this->alice)
            ->setIdentifiant('alice2@tkdo.org')
            ->setNom('Alice2')
            ->setMdp('nouveaumdpalice');
        $this->repository->update($aliceModifiee);

        $this->assertEquals($aliceModifiee, $this->repository->read($this->alice->getId()));
    }

    public function testUpdateUtilisateurInconnu()
    {
        $this->expectException(UtilisateurNotFoundException::class);
        $this->repository->update($this->inconnu);
    }
}
