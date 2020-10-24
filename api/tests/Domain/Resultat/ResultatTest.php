<?php
declare(strict_types=1);

namespace Tests\Domain\Resultat;

use App\Domain\Resultat\Resultat;
use Tests\TestCase;

class ResultatTest extends TestCase
{
    public function resultatProvider()
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
     * @dataProvider resultatProvider
     * @param $id
     * @param $resultatname
     * @param $firstName
     * @param $lastName
     */
    public function testGetters($id, $resultatname, $firstName, $lastName)
    {
        $resultat = new Resultat($id, $resultatname, $firstName, $lastName);

        $this->assertEquals($id, $resultat->getId());
        $this->assertEquals($resultatname, $resultat->getResultatname());
        $this->assertEquals($firstName, $resultat->getFirstName());
        $this->assertEquals($lastName, $resultat->getLastName());
    }

    /**
     * @dataProvider resultatProvider
     * @param $id
     * @param $resultatname
     * @param $firstName
     * @param $lastName
     */
    public function testJsonSerialize($id, $resultatname, $firstName, $lastName)
    {
        $resultat = new Resultat($id, $resultatname, $firstName, $lastName);

        $expectedPayload = json_encode([
            'id' => $id,
            'username' => $resultatname,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);

        $this->assertEquals($expectedPayload, json_encode($resultat));
    }
}
