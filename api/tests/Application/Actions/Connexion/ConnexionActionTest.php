<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Utilisateur\UtilisateurNotFoundException;
use Slim\Exception\HttpBadRequestException;
use Tests\Application\Actions\ActionTestCase;

class CreateConnexionActionTest extends ActionTestCase
{
    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->readOneByIdentifiant($this->alice->getIdentifiant())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $response = $this->handleRequest('POST', '/connexion', '', <<<EOT
{
    "identifiant": "{$this->alice->getIdentifiant()}",
    "mdp": "{$this->mdpalice}"
}

EOT
        );

        $token = json_decode((string) $response->getBody())->token;
        $estAdmin = json_encode($this->alice->getEstAdmin());
        $json = <<<EOT
{
    "token": "{$token}",
    "utilisateur": {
        "id": {$this->alice->getId()},
        "nom": "{$this->alice->getNom()}",
        "estAdmin": $estAdmin
    }
}

EOT;
        $this->assertEquals($json, (string) $response->getBody());
    }

    public function testActionIdentifiantInvalides()
    {
        $this->utilisateurRepositoryProphecy
            ->readOneByIdentifiant($this->alice->getIdentifiant())
            ->willThrow(new UtilisateurNotFoundException())
            ->shouldBeCalledOnce();

        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('identifiants invalides');
        $this->handleRequest(
            'POST',
            '/connexion',
            '',
            <<<EOT
{
    "identifiant": "{$this->alice->getIdentifiant()}",
    "mdp": "{$this->mdpalice}"
}

EOT
        );
    }

    public function testActionMdpInvalides()
    {
        $this->utilisateurRepositoryProphecy
            ->readOneByIdentifiant($this->alice->getIdentifiant())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('identifiants invalides');
        $this->handleRequest(
            'POST',
            '/connexion',
            '',
            <<<EOT
{
    "identifiant": "{$this->alice->getIdentifiant()}",
    "mdp": "mauvaismdp"
}

EOT
        );
    }
}
