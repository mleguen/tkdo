<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class ConnexionActionTest extends ActionTestCase
{
    /**
     * @var Utilisateur
     */
    private $alice;

    public function setUp() {
        parent::setUp();
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
    }

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
            ->willThrow(new UtilisateurInconnuException())
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
