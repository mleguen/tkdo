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

    public function testFindAllUtilisateurByDefault()
    {
        $utilisateurs = [
            1 => new Utilisateur(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Utilisateur(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Utilisateur(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Utilisateur(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Utilisateur(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];

        $utilisateurRepository = new InMemoryUtilisateurRepository();

        $this->assertEquals(array_values($utilisateurs), $utilisateurRepository->findAll());
    }

    public function testFindUtilisateurOfId()
    {
        $utilisateur = new Utilisateur(1, 'bill.gates', 'Bill', 'Gates');

        $utilisateurRepository = new InMemoryUtilisateurRepository([1 => $utilisateur]);

        $this->assertEquals($utilisateur, $utilisateurRepository->findUtilisateurOfId(1));
    }

    public function testFindUtilisateurOfIdThrowsNotFoundException()
    {
        $utilisateurRepository = new InMemoryUtilisateurRepository([]);
        $this->expectException(UtilisateurNotFoundException::class);
        $utilisateurRepository->findUtilisateurOfId(1);
    }
}
