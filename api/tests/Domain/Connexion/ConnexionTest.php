<?php

declare(strict_types=1);

namespace Tests\Domain\Connexion;

use App\Domain\Connexion\Connexion;
use Tests\TestCase;

class ConnexionTest extends TestCase
{
    public function connexionProvider(): array
    {
        return [
            [1, 'bill.gates', 'Bill', 'Gates'],
            [2, 'steve.jobs', 'Steve', 'Jobs'],
            [3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'],
            [4, 'evan.spiegel', 'Evan', 'Spiegel'],
            [5, 'jack.dorsey', 'Jack', 'Dorsey'],
        ];
    }

    /**
     * @dataProvider connexionProvider
     * @param int    $id
     * @param string $connexionname
     * @param string $firstName
     * @param string $lastName
     */
    public function testGetters(int $id, string $connexionname, string $firstName, string $lastName)
    {
        $connexion = new Connexion($id, $connexionname, $firstName, $lastName);

        $this->assertEquals($id, $connexion->getId());
        $this->assertEquals($connexionname, $connexion->getConnexionname());
        $this->assertEquals($firstName, $connexion->getFirstName());
        $this->assertEquals($lastName, $connexion->getLastName());
    }

    /**
     * @dataProvider connexionProvider
     * @param int    $id
     * @param string $connexionname
     * @param string $firstName
     * @param string $lastName
     */
    public function testJsonSerialize(int $id, string $connexionname, string $firstName, string $lastName)
    {
        $connexion = new Connexion($id, $connexionname, $firstName, $lastName);

        $expectedPayload = json_encode([
            'id' => $id,
            'username' => $connexionname,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);

        $this->assertEquals($expectedPayload, json_encode($connexion));
    }
}
