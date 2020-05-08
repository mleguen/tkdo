<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Idee;

use App\Infrastructure\Persistence\Idee\InMemoryIdee;
use App\Infrastructure\Persistence\Idee\InMemoryIdeeRepository;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use Tests\TestCase;

class InMemoryIdeeRepositoryTest extends TestCase
{
    /**
     * @var InMemoryUtilisateur
     */
    private $alice;

    /**
     * @var InMemoryUtilisateur
     */
    private $bob;

    /**
     * @var InMemoryIdee
     */
    private $ideeAlicePourAlice;

    /**
     * @var InMemoryIdee
     */
    private $ideeBobPourAlice;

    /**
     * @var InMemoryIdeeRepository
     */
    private $repository;

    public function setUp()
    {
        $this->alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'mdpalice', 'Alice');
        $this->bob = new InMemoryUtilisateur(1, 'bob@tkdo.org', 'Bob', 'Bob');
        $this->ideeAlicePourAlice = new InMemoryIdee(
            0,
            $this->alice,
            "un gauffrier",
            $this->alice,
            \DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-19T00:00:00+0000')
        );
        $this->ideeBobPourAlice = new InMemoryIdee(
            1,
            $this->alice,
            "une canne à pêche",
            $this->bob,
            \DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-20T00:00:00+0000')
        );
        $this->repository = new InMemoryIdeeRepository([
            0 => $this->ideeAlicePourAlice,
            1 => $this->ideeBobPourAlice,
        ]);
    }

    public function testCreate()
    {
        $createParams = [
            $this->bob,
            "des gants de boxe",
            $this->bob,
            \DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-07T00:00:00+0000')
        ];
        $this->assertEquals(new InMemoryIdee(2, ...$createParams), $this->repository->create(...$createParams));
    }

    public function testRead()
    {
        $this->assertEquals($this->ideeAlicePourAlice, $this->repository->read(0));
    }

    /**
     * @expectedException \App\Domain\Idee\IdeeInconnueException
     */
    public function testReadIdeeInconnue()
    {
        $this->repository->read(2);
    }

    public function testReadByUtilisateur()
    {
        $this->assertEquals(
            [$this->ideeAlicePourAlice, $this->ideeBobPourAlice],
            $this->repository->readByUtilisateur($this->alice)
        );
    }

    public function testReadByUtilisateurAucuneIdee()
    {
        $this->assertEquals(
            [],
            $this->repository->readByUtilisateur($this->bob)
        );
    }

    public function testDelete()
    {
        $this->repository->delete($this->repository->getReference(0));
        $this->assertEquals([$this->ideeBobPourAlice], $this->repository->readByUtilisateur($this->alice));
    }

    /**
     * @expectedException \App\Domain\Idee\IdeeInconnueException
     */
    public function testDeleteIdeeInconnue()
    {
        $this->repository->delete($this->repository->getReference(3));
    }
}
