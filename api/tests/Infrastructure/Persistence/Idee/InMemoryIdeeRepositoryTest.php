<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Idee;

use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Idee\InMemoryIdeeRepository;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurRepository;
use Tests\TestCase;

class InMemoryIdeeRepositoryTest extends TestCase
{
    /**
     * @var DoctrineUtilisateur
     */
    private $alice;

    /**
     * @var DoctrineUtilisateur
     */
    private $bob;

    /**
     * @var DoctrineIdee
     */
    private $ideeAlicePourAlice;

    /**
     * @var DoctrineIdee
     */
    private $ideeBobPourAlice;

    /**
     * @var DoctrineIdee
     */
    private $ideeInconnue;

    /**
     * @var InMemoryIdeeRepository
     */
    private $repository;

    public function setUp()
    {
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $this->bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');

        $this->ideeAlicePourAlice = (new DoctrineIdee(1))
            ->setUtilisateur($this->alice)
            ->setDescription('un gauffrier')
            ->setAuteur($this->alice)
            ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-19T00:00:00+0000'));
        $this->ideeBobPourAlice = (new DoctrineIdee(2))
            ->setUtilisateur($this->alice)
            ->setDescription('une canne à pêche')
            ->setAuteur($this->bob)
            ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-20T00:00:00+0000'));
        $this->ideeInconnue = (new DoctrineIdee(3))
            ->setUtilisateur($this->bob)
            ->setDescription('des gants de boxe')
            ->setAuteur($this->bob)
            ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-07T00:00:00+0000'));

        $utilisateurRepositoryProphecy = $this->prophesize(InMemoryUtilisateurRepository::class);
        $utilisateurRepositoryProphecy->readNoClone($this->bob->getId())->willReturn($this->bob);

        $this->repository = new InMemoryIdeeRepository(
            [
                $this->ideeAlicePourAlice->getId() => $this->ideeAlicePourAlice,
                $this->ideeBobPourAlice->getId() => $this->ideeBobPourAlice,
            ],
            $utilisateurRepositoryProphecy->reveal()
        );
    }

    public function testCreate()
    {
        $this->assertEquals($this->ideeInconnue, $this->repository->create(
            $this->ideeInconnue->getUtilisateur(),
            $this->ideeInconnue->getDescription(),
            $this->ideeInconnue->getAuteur(),
            $this->ideeInconnue->getDateProposition()
        ));
    }

    public function testRead()
    {
        $this->assertEquals($this->ideeAlicePourAlice, $this->repository->read($this->ideeAlicePourAlice->getId()));
    }

    public function testReadReference()
    {
        $this->assertEquals(
            new DoctrineIdee($this->ideeAlicePourAlice->getId()),
            $this->repository->read($this->ideeAlicePourAlice->getId(), true)
        );
    }

    /**
     * @expectedException \App\Domain\Idee\IdeeInconnueException
     */
    public function testReadIdeeInconnue()
    {
        $this->repository->read($this->ideeInconnue->getId());
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
        $this->repository->delete($this->ideeAlicePourAlice);
        $this->assertEquals([$this->ideeBobPourAlice], $this->repository->readByUtilisateur($this->alice));
    }

    /**
     * @expectedException \App\Domain\Idee\IdeeInconnueException
     */
    public function testDeleteIdeeInconnue()
    {
        $this->repository->delete($this->ideeInconnue);
    }
}
