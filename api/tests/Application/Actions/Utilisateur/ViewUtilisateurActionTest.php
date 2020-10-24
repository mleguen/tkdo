<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Domain\Utilisateur\UtilisateurNotFoundException;
use Tests\Application\Actions\ActionTestCase;

class ViewUtilisateurActionTest extends ActionTestCase
{
    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            'GET',
            "/utilisateur/{$this->alice->getId()}"
        );

        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "id": {$this->alice->getId()},
    "identifiant": "{$this->alice->getIdentifiant()}",
    "nom": "{$this->alice->getNom()}"
}
EOT
            , $response
        );
    }

    public function testActionAutreUtilisateur()
    {
        $response = $this->handleAuthRequest(
            $this->bob->getId(),
            'GET',
            "/utilisateur/{$this->alice->getId()}"
        );

        $this->assertEqualsResponse(
            403,
            <<<'EOT'
{
    "type": "INSUFFICIENT_PRIVILEGES",
    "description": "Forbidden."
}
EOT
            , $response
        );
    }

    public function testActionUtilisateurInconnu()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willThrow(new UtilisateurNotFoundException())
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            'GET',
            "/utilisateur/{$this->alice->getId()}"
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

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest('GET', "/utilisateur/{$this->alice->getId()}");

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
