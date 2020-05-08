<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Idee\InMemoryIdee;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class IdeeReadAllActionTest extends ActionTestCase
{
    /**
     * @var Utilisateur
     */
    private $alice;

    public function setUp()
    {
        parent::setup();
        $this->alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'Alice', 'Alice');
    }

    public function testAction()
    {
        $bob = new InMemoryUtilisateur(1, 'bob@tkdo.org', 'Bob', 'Bob');

        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $dateProposition = '2020-04-19T00:00:00+0000';
        $idee = new InMemoryIdee(
            0,
            $this->alice,
            "un gauffrier",
            $this->alice,
            \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $dateProposition)
        );
        
        $this->ideeRepositoryProphecy
            ->readByUtilisateur($this->alice)
            ->willReturn([$idee])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest('GET', "/utilisateur/{$this->alice->getId()}/idee");

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
        {
            "id": {$idee->getId()},
            "description": "{$idee->getDescription()}",
            "auteur": {
                "id": {$idee->getAuteur()->getId()},
                "identifiant": "{$idee->getAuteur()->getIdentifiant()}",
                "nom": "{$idee->getAuteur()->getNom()}"
            },
            "dateProposition": "$dateProposition"
        }
    ]
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

        $response = $this->handleAuthorizedRequest('GET', "/utilisateur/{$this->alice->getId()}/idee");

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

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest('GET', "/utilisateur/{$this->alice->getId()}/idee");

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
