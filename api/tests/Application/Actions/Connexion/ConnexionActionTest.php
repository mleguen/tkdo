<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Utilisateur\UtilisateurNotFoundException;
use Tests\Application\Actions\ActionTestCase;

class CreateConnexionActionTest extends ActionTestCase
{
    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->readOneByIdentifiants($this->alice->getIdentifiant(), $this->alice->getMdp())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $response = $this->handleRequest('POST', '/connexion', '', <<<EOT
{
    "identifiant": "{$this->alice->getIdentifiant()}",
    "mdp": "{$this->alice->getMdp()}"
}
EOT
        );

        $token = json_decode((string) $response->getBody())->token;
        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "token": "{$token}",
    "utilisateur": {
        "id": {$this->alice->getId()},
        "nom": "{$this->alice->getNom()}"
    }
}
EOT
            , $response
        );
    }

    public function testActionIdentifiantsInvalides()
    {
        $this->utilisateurRepositoryProphecy
            ->readOneByIdentifiants($this->alice->getIdentifiant(), $this->alice->getMdp())
            ->willThrow(new UtilisateurNotFoundException())
            ->shouldBeCalledOnce();

        $response = $this->handleRequest(
            'POST',
            '/connexion',
            '',
            <<<EOT
{
    "identifiant": "{$this->alice->getIdentifiant()}",
    "mdp": "{$this->alice->getMdp()}"
}
EOT
        );

        $this->assertEqualsResponse(
            400,
            <<<'EOT'
{
    "type": "BAD_REQUEST",
    "description": "identifiants invalides"
}
EOT
            , $response
        );
    }
}
