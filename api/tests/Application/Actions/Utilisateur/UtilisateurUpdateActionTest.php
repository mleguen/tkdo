<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class UtilisateurUpdateActionTest extends ActionTestCase
{
    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        /**
         * @var DoctrineUtilisateur
         */
        $aliceModifiee = (new DoctrineUtilisateur($this->alice->getId()))
            ->setIdentifiant('alice2@tkdo.org')
            ->setNom('Alice2')
            ->setMdp('nouveaumdpalice');
        $this->utilisateurRepositoryProphecy
            ->update($aliceModifiee)
            ->willReturn($aliceModifiee)
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            'PUT',
            "/utilisateur/{$this->alice->getId()}",
            '',
            <<<EOT
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

    public function testActionAutreUtilisateur()
    {
        $response = $this->handleAuthRequest(
            $this->bob->getId(),
            'PUT',
            "/utilisateur/{$this->alice->getId()}", '', "{}"
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
            ->willThrow(new UtilisateurInconnuException())
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            'PUT',
            "/utilisateur/{$this->alice->getId()}", '', "{}"
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
