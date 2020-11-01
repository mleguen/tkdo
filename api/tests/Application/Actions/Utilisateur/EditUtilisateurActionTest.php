<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Tests\Application\Actions\ActionTestCase;

class EditUtilisateurActionTest extends ActionTestCase
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
            ->setGenre('M')
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
    "genre": "{$aliceModifiee->getGenre()}",
    "identifiant": "{$aliceModifiee->getIdentifiant()}",
    "mdp": "{$aliceModifiee->getMdp()}",
    "nom": "{$aliceModifiee->getNom()}"
}
EOT
        );

        $this->assertEquals('null', (string)$response->getBody());
    }

    public function testActionAutreUtilisateur()
    {
        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->bob->getId(),
            'PUT',
            "/utilisateur/{$this->alice->getId()}", '', "{}"
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
            'PUT',
            "/utilisateur/{$this->alice->getId()}", '', "{}"
        );
    }

    public function testActionNonAutorise()
    {
        $this->expectException(HttpUnauthorizedException::class);
        $this->handleRequest('PUT', "/utilisateur/{$this->alice->getId()}");
    }
}
