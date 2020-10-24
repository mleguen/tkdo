<?php
declare(strict_types=1);

namespace Tests\Domain\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use Tests\TestCase;

class UtilisateurTest extends TestCase
{
    public function utilisateurProvider()
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
     * @dataProvider utilisateurProvider
     * @param $id
     * @param $utilisateurname
     * @param $firstName
     * @param $lastName
     */
    public function testGetters($id, $utilisateurname, $firstName, $lastName)
    {
        $utilisateur = new Utilisateur($id, $utilisateurname, $firstName, $lastName);

        $this->assertEquals($id, $utilisateur->getId());
        $this->assertEquals($utilisateurname, $utilisateur->getUtilisateurname());
        $this->assertEquals($firstName, $utilisateur->getFirstName());
        $this->assertEquals($lastName, $utilisateur->getLastName());
    }

    /**
     * @dataProvider utilisateurProvider
     * @param $id
     * @param $utilisateurname
     * @param $firstName
     * @param $lastName
     */
    public function testJsonSerialize($id, $utilisateurname, $firstName, $lastName)
    {
        $utilisateur = new Utilisateur($id, $utilisateurname, $firstName, $lastName);

        $expectedPayload = json_encode([
            'id' => $id,
            'username' => $utilisateurname,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);

        $this->assertEquals($expectedPayload, json_encode($utilisateur));
    }
}
