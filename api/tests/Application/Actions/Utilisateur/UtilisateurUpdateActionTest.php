<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
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
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
    }

    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        /**
         * @var DoctrineUtilisateur
         */
        $aliceModifiee = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice2@tkdo.org')
            ->setNom('Alice2')
            ->setMdp('nouveaumdpalice');
        $this->utilisateurRepositoryProphecy
            ->update($aliceModifiee)
            ->willReturn($aliceModifiee)
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest('PUT', "/utilisateur/{$this->alice->getId()}", <<<EOT
{
    "identifiant": "{$aliceModifiee->getIdentifiant()}",
    "mdp": "{$aliceModifiee->getMdp()}",
    "nom": "{$aliceModifiee->getNom()}"
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

        $response = $this->handleAuthorizedRequest('PUT', "/utilisateur/{$this->alice->getId()}", "{}");

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
