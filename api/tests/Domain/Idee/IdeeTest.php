<?php
declare(strict_types=1);

namespace Tests\Domain\Idee;

use App\Domain\Idee\Idee;
use Tests\TestCase;

class IdeeTest extends TestCase
{
    public function ideeProvider()
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
     * @dataProvider ideeProvider
     * @param $id
     * @param $ideename
     * @param $firstName
     * @param $lastName
     */
    public function testGetters($id, $ideename, $firstName, $lastName)
    {
        $idee = new Idee($id, $ideename, $firstName, $lastName);

        $this->assertEquals($id, $idee->getId());
        $this->assertEquals($ideename, $idee->getIdeename());
        $this->assertEquals($firstName, $idee->getFirstName());
        $this->assertEquals($lastName, $idee->getLastName());
    }

    /**
     * @dataProvider ideeProvider
     * @param $id
     * @param $ideename
     * @param $firstName
     * @param $lastName
     */
    public function testJsonSerialize($id, $ideename, $firstName, $lastName)
    {
        $idee = new Idee($id, $ideename, $firstName, $lastName);

        $expectedPayload = json_encode([
            'id' => $id,
            'username' => $ideename,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);

        $this->assertEquals($expectedPayload, json_encode($idee));
    }
}
