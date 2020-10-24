<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Domain\Utilisateur\UtilisateurNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
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

        $json = <<<EOT
{
    "id": {$this->alice->getId()},
    "identifiant": "{$this->alice->getIdentifiant()}",
    "nom": "{$this->alice->getNom()}"
}
EOT;
        $this->assertEquals($json, $response->getBody());
    }

    public function testActionAutreUtilisateur()
    {
        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->bob->getId(),
            'GET',
            "/utilisateur/{$this->alice->getId()}"
        );
    }

    public function testActionUtilisateurInconnu()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willThrow(new UtilisateurNotFoundException())
            ->shouldBeCalledOnce();

        $this->expectException(HttpNotFoundException::class);
        $this->expectExceptionMessage('utilisateur inconnu');
        $this->handleAuthRequest(
            $this->alice->getId(),
            'GET',
            "/utilisateur/{$this->alice->getId()}"
        );
    }

    public function testActionNonAutorise()
    {
        $this->expectException(HttpUnauthorizedException::class);
        $this->handleRequest('GET', "/utilisateur/{$this->alice->getId()}");
    }
}
