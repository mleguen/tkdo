<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class UtilisateurUpdateActionTest extends ActionTestCase
{
    /**
     * @var Utilisateur
     */
    private $alice;

    public function setUp()
    {
        parent::setUp();
        $this->alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'Alice', 'Alice');
    }

    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $aliceModifie = new InMemoryUtilisateur($this->alice->getId(), 'alice2@tkdo.org', 'nouveaumdpalice', 'Alice2');
        $this->utilisateurRepositoryProphecy
            ->update($aliceModifie)
            ->willReturn($aliceModifie)
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest('PUT', "/utilisateur/{$this->alice->getId()}", <<<EOT
{
    "identifiant": "{$aliceModifie->getIdentifiant()}",
    "mdp": "{$aliceModifie->getMdp()}",
    "nom": "{$aliceModifie->getNom()}"
}
EOT
        );

        $this->assertEqualsResponse(
            200,
            <<<'EOT'
null
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

        $response = $this->handleAuthorizedRequest('PUT', "/utilisateur/{$this->alice->getId()}");

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
        $response = $this->handleRequest('PUT', "/utilisateur/{$this->alice->getId()}");

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
