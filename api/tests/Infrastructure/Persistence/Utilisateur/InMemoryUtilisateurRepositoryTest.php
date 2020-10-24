<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurRepository;
use Tests\TestCase;

class InMemoryUtilisateurRepositoryTest extends TestCase
{
    public function testFindAll()
    {
        $utilisateur = new Utilisateur(1, 'bill.gates', 'Bill', 'Gates');

        $utilisateurRepository = new InMemoryUtilisateurRepository([1 => $utilisateur]);

        $this->assertEquals([$utilisateur], $utilisateurRepository->findAll());
    }

    public function testFindUtilisateurOfId()
    {
        $utilisateur = new Utilisateur(1, 'bill.gates', 'Bill', 'Gates');

        $utilisateurRepository = new InMemoryUtilisateurRepository([1 => $utilisateur]);

        $this->assertEquals($utilisateur, $utilisateurRepository->findUtilisateurOfId(1));
    }

    /**
     * @expectedException \App\Domain\Utilisateur\UtilisateurNotFoundException
     */
    public function testFindUtilisateurOfIdThrowsNotFoundException()
    {
        $utilisateurRepository = new InMemoryUtilisateurRepository([]);
        $utilisateurRepository->findUtilisateurOfId(1);
    }
}
