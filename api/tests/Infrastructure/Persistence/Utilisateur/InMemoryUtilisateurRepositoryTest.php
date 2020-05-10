<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Utilisateur;

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

    public function setUp()
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

    /**
     * @expectedException \App\Domain\Utilisateur\UtilisateurInconnuException
     */
    public function testReadUtilisateurInconnu()
    {
        $this->repository->read($this->inconnu->getId());
    }

    public function testReadOneByIdentifiants()
    {
        $this->assertEquals($this->alice, $this->repository->readOneByIdentifiants('alice@tkdo.org', 'mdpalice'));
    }

    /**
     * @expectedException \App\Domain\Utilisateur\UtilisateurInconnuException
     */
    public function testReadOneByIdentifiantsMauvaisIdentifiant()
    {
        $this->repository->readOneByIdentifiants('mauvaisIdentifiant', 'mdpalice');
    }

    /**
     * @expectedException \App\Domain\Utilisateur\UtilisateurInconnuException
     */
    public function testReadOneByIdentifiantsMauvaisMdp()
    {
        $this->repository->readOneByIdentifiants('alice@tkdo.org', 'mauvaisMdp');
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

    /**
     * @expectedException \App\Domain\Utilisateur\UtilisateurInconnuException
     */
    public function testUpdateUtilisateurInconnu()
    {
        $this->repository->update($this->inconnu);
    }
}
