<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Application\Mock\MockData;
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

        $response = $this->handleAuthorizedRequest('POST', '/connexion', <<<EOT
{
    "identifiant": "{$this->alice->getIdentifiant()}",
    "mdp": "{$this->alice->getMdp()}"
}
EOT
        );

        $token = MockData::getToken();
        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "idUtilisateur": {$this->alice->getId()},
    "token": "{$token}"
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

        $response = $this->handleAuthorizedRequest(
            'POST',
            '/connexion',
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
