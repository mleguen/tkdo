<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurReference;
use Exception;
use Tests\Application\Actions\ActionTestCase;

class IdeeReadAllActionTest extends ActionTestCase
{
    /**
     * @var DoctrineIdee
     */
    private $idee;

    public function setUp()
    {
        parent::setup();
        $alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');
        $this->utilisateurRepositoryProphecy
            ->read($bob->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($bob->getId()));

        $dateProposition = '2020-04-19T00:00:00+0000';
        $this->idee = (new DoctrineIdee(1))
            ->setUtilisateur($alice)
            ->setDescription('un gauffrier')
            ->setAuteur($bob)
            ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, $dateProposition));
    }

    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->idee->getUtilisateur()->getId())
            ->willReturn($this->idee->getUtilisateur())
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->readByUtilisateur($this->idee->getUtilisateur())
            ->willReturn([$this->idee])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'GET',
            '/idee',
            "idUtilisateur={$this->idee->getUtilisateur()->getId()}"
        );

        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "utilisateur": {
        "id": {$this->idee->getUtilisateur()->getId()},
        "identifiant": "{$this->idee->getUtilisateur()->getIdentifiant()}",
        "nom": "{$this->idee->getUtilisateur()->getNom()}"
    },
    "idees": [
        {
            "id": {$this->idee->getId()},
            "description": "{$this->idee->getDescription()}",
            "auteur": {
                "id": {$this->idee->getAuteur()->getId()},
                "identifiant": "{$this->idee->getAuteur()->getIdentifiant()}",
                "nom": "{$this->idee->getAuteur()->getNom()}"
            },
            "dateProposition": "{$this->idee->getDateProposition()->format(\DateTimeInterface::ISO8601)}"
        }
    ]
}
EOT
            , $response
        );
    }

    public function testActionIdUtilisateurManquant()
    {
        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'GET',
            '/idee',
            ''
        );

        $this->assertEqualsResponse(
            400,
            <<<'EOT'
{
    "type": "BAD_REQUEST",
    "description": "idUtilisateur manquant"
}
EOT
            , $response
        );
    }

    public function testActionUtilisateurInconnu()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->idee->getUtilisateur()->getId())
            ->willThrow(new UtilisateurInconnuException())
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'GET',
            '/idee',
            "idUtilisateur={$this->idee->getUtilisateur()->getId()}"
        );

        $this->assertEqualsResponse(
            404,
            <<<'EOT'
{
    "type": "RESOURCE_NOT_FOUND",
    "description": "utilisateur inconnu"
}
EOT
            , $response
        );
    }

    public function testActionEchecReadByUtilisateur()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->idee->getUtilisateur()->getId())
            ->willReturn($this->idee->getUtilisateur())
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->readByUtilisateur($this->idee->getUtilisateur())
            ->willThrow(new Exception('erreur pendant readByUtilisateur'))
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'GET',
            '/idee',
            "idUtilisateur={$this->idee->getUtilisateur()->getId()}"
        );

        $this->assertEqualsResponse(
            500,
            <<<'EOT'
{
    "type": "SERVER_ERROR",
    "description": "erreur pendant readByUtilisateur"
}
EOT
            , $response
        );
    }

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest('GET', '/idee', "idUtilisateur={$this->idee->getUtilisateur()->getId()}");

        $this->assertEqualsResponse(
            401,
            <<<'EOT'
{
    "type": "UNAUTHENTICATED",
    "description": "Unauthorized."
}
EOT
            , $response
        );
    }
}
