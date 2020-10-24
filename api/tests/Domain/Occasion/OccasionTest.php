<?php
declare(strict_types=1);

namespace Tests\Domain\Occasion;

use App\Domain\Occasion\Occasion;
use Tests\TestCase;

class OccasionTest extends TestCase
{
    public function occasionProvider()
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
     * @dataProvider occasionProvider
     * @param $id
     * @param $occasionname
     * @param $firstName
     * @param $lastName
     */
    public function testGetters($id, $occasionname, $firstName, $lastName)
    {
        $occasion = new Occasion($id, $occasionname, $firstName, $lastName);

        $this->assertEquals($id, $occasion->getId());
        $this->assertEquals($occasionname, $occasion->getOccasionname());
        $this->assertEquals($firstName, $occasion->getFirstName());
        $this->assertEquals($lastName, $occasion->getLastName());
    }

    /**
     * @dataProvider occasionProvider
     * @param $id
     * @param $occasionname
     * @param $firstName
     * @param $lastName
     */
    public function testJsonSerialize($id, $occasionname, $firstName, $lastName)
    {
        $occasion = new Occasion($id, $occasionname, $firstName, $lastName);

        $expectedPayload = json_encode([
            'id' => $id,
            'username' => $occasionname,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);

        $this->assertEquals($expectedPayload, json_encode($occasion));
    }
}
