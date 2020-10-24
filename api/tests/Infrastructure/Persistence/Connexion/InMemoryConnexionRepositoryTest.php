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

    public function testFindConnexionOfId()
    {
        $connexion = new Connexion(1, 'bill.gates', 'Bill', 'Gates');

        $connexionRepository = new InMemoryConnexionRepository([1 => $connexion]);

        $this->assertEquals($connexion, $connexionRepository->findConnexionOfId(1));
    }

    /**
     * @expectedException \App\Domain\Connexion\ConnexionNotFoundException
     */
    public function testFindConnexionOfIdThrowsNotFoundException()
    {
        $connexionRepository = new InMemoryConnexionRepository([]);
        $connexionRepository->findConnexionOfId(1);
    }
}
