<?php

declare(strict_types=1);

namespace Tests\Domain\Idee;

use App\Domain\Idee\Idee;
use Tests\TestCase;

class IdeeTest extends TestCase
{
    public static function ideeProvider(): array
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
     * @param int    $id
     * @param string $ideename
     * @param string $firstName
     * @param string $lastName
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('ideeProvider')]
    public function testGetters(int $id, string $ideename, string $firstName, string $lastName)
    {
        $idee = new Idee($id, $ideename, $firstName, $lastName);

        $this->assertEquals($id, $idee->getId());
        $this->assertEquals($ideename, $idee->getIdeename());
        $this->assertEquals($firstName, $idee->getFirstName());
        $this->assertEquals($lastName, $idee->getLastName());
    }

    /**
     * @param int    $id
     * @param string $ideename
     * @param string $firstName
     * @param string $lastName
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('ideeProvider')]
    public function testJsonSerialize(int $id, string $ideename, string $firstName, string $lastName)
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
