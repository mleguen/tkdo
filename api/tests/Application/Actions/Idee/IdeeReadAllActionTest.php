<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurReference;
use Exception;
use Tests\Application\Actions\ActionTestCase;

class IdeeReadAllActionTest extends ActionTestCase
{
    /**
     * @var DoctrineIdee
     */
    private $ideeDeBobPourAlice;

    /**
     * @var DoctrineIdee
     */
    private $ideeDeAlicePourElleMeme;

    public function setUp()
    {
        parent::setup();
        $this->utilisateurRepositoryProphecy
            ->read($this->bob->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($this->bob->getId()));

        $this->ideeDeBobPourAlice = (new DoctrineIdee(1))
            ->setUtilisateur($this->alice)
            ->setDescription('une idee proposee par Bob')
            ->setAuteur($this->bob)
            ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-04-19T00:00:00+0000'));
        $this->ideeDeAlicePourElleMeme = (new DoctrineIdee(2))
            ->setUtilisateur($this->alice)
            ->setDescription('une idee proposee par Alice elle-meme')
            ->setAuteur($this->alice)
            ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2020-10-22T00:00:00+0000'));
    }

    public function testActionTiers()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->readByUtilisateur($this->alice)
            ->willReturn([
                $this->ideeDeBobPourAlice,
                $this->ideeDeAlicePourElleMeme,
            ])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->charlie->getId(),
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}"
        );

        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "utilisateur": {
        "id": {$this->alice->getId()},
        "identifiant": "{$this->alice->getIdentifiant()}",
        "nom": "{$this->alice->getNom()}"
    },
    "idees": [
{$this->morceauReponseIdee($this->ideeDeBobPourAlice)},
{$this->morceauReponseIdee($this->ideeDeAlicePourElleMeme)}
    ]
}
EOT
            , $response
        );
    }

    public function testActionAuteur()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->readByUtilisateur($this->alice)
            ->willReturn([
                $this->ideeDeBobPourAlice,
                $this->ideeDeAlicePourElleMeme,
            ])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->bob->getId(),
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}"
        );

        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "utilisateur": {
        "id": {$this->alice->getId()},
        "identifiant": "{$this->alice->getIdentifiant()}",
        "nom": "{$this->alice->getNom()}"
    },
    "idees": [
{$this->morceauReponseIdee($this->ideeDeBobPourAlice)},
{$this->morceauReponseIdee($this->ideeDeAlicePourElleMeme)}
    ]
}
EOT
            , $response
        );
    }

    public function testActionUtilisateur()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->readByUtilisateur($this->alice)
            ->willReturn([
                $this->ideeDeBobPourAlice,
                $this->ideeDeAlicePourElleMeme,
            ])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}"
        );

        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "utilisateur": {
        "id": {$this->alice->getId()},
        "identifiant": "{$this->alice->getIdentifiant()}",
        "nom": "{$this->alice->getNom()}"
    },
    "idees": [
{$this->morceauReponseIdee($this->ideeDeAlicePourElleMeme)}
    ]
}
EOT
            , $response
        );
    }

    private function morceauReponseIdee(Idee $i)
    {
        return <<<EOT
        {
            "id": {$i->getId()},
            "description": "{$i->getDescription()}",
            "auteur": {
                "id": {$i->getAuteur()->getId()},
                "identifiant": "{$i->getAuteur()->getIdentifiant()}",
                "nom": "{$i->getAuteur()->getNom()}"
            },
            "dateProposition": "{$i->getDateProposition()->format(\DateTimeInterface::ISO8601)}"
        }
EOT;
    }

    public function testActionIdUtilisateurManquant()
    {
        $response = $this->handleAuthRequest(
            $this->charlie->getId(),
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
            ->read($this->alice->getId())
            ->willThrow(new UtilisateurInconnuException())
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->charlie->getId(),
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}"
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
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->readByUtilisateur($this->alice)
            ->willThrow(new Exception('erreur pendant readByUtilisateur'))
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->charlie->getId(),
            'GET',
            '/idee',
            "idUtilisateur={$this->alice->getId()}"
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
        $response = $this->handleRequest('GET', '/idee', "idUtilisateur={$this->alice->getId()}");

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
