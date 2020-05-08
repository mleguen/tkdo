<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Utilisateur;

use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurRepository;
use Tests\TestCase;

class InMemoryUserRepositoryTest extends TestCase
{
    /**
     * @var InMemoryUtilisateur
     */
    private $alice;

    /**
     * @var InMemoryUtilisateurRepository
     */
    private $repository;

    public function setUp()
    {
        $this->alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'mdpalice', 'Alice');
        $this->repository = new InMemoryUtilisateurRepository([0 => $this->alice]);
    }
    
    public function testRead()
    {
        $this->assertEquals($this->alice, $this->repository->read(0));
    }

    /**
     * @expectedException \App\Domain\Utilisateur\UtilisateurInconnuException
     */
    public function testReadUtilisateurInconnu()
    {
        $this->repository->read(1);
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
        $aliceModifiee = new InMemoryUtilisateur(0, 'alice2@tkdo.org', 'mdpalice2', 'Alice2');
        $this->repository->update($aliceModifiee);

        $this->assertEquals($aliceModifiee, $this->repository->read(0));
    }

    /**
     * @expectedException \App\Domain\Utilisateur\UtilisateurInconnuException
     */
    public function testUpdateUtilisateurInconnu()
    {
        $bob = new InMemoryUtilisateur(1, 'bob@tkdo.org', 'Bob', 'Bob');
        $this->repository->update($bob);
    }
}
