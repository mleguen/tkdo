<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Connexion;

use App\Domain\Connexion\Connexion;
use App\Domain\Connexion\ConnexionNotFoundException;
use App\Infrastructure\Persistence\Connexion\InMemoryConnexionRepository;
use Tests\TestCase;

class InMemoryConnexionRepositoryTest extends TestCase
{
    public function testFindAll()
    {
        $connexion = new Connexion(1, 'bill.gates', 'Bill', 'Gates');

        $connexionRepository = new InMemoryConnexionRepository([1 => $connexion]);

        $this->assertEquals([$connexion], $connexionRepository->findAll());
    }

    public function testFindAllConnexionByDefault()
    {
        $connexions = [
            1 => new Connexion(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Connexion(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Connexion(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Connexion(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Connexion(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];

        $connexionRepository = new InMemoryConnexionRepository();

        $this->assertEquals(array_values($connexions), $connexionRepository->findAll());
    }

    public function testFindConnexionOfId()
    {
        $connexion = new Connexion(1, 'bill.gates', 'Bill', 'Gates');

        $connexionRepository = new InMemoryConnexionRepository([1 => $connexion]);

        $this->assertEquals($connexion, $connexionRepository->findConnexionOfId(1));
    }

    public function testFindConnexionOfIdThrowsNotFoundException()
    {
        $connexionRepository = new InMemoryConnexionRepository([]);
        $this->expectException(ConnexionNotFoundException::class);
        $connexionRepository->findConnexionOfId(1);
    }
}
