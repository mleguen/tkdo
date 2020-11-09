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
            ->setMdp('nouveaumdpalice')
            ->setEstAdmin($this->alice->getEstAdmin());
        $this->utilisateurRepositoryProphecy
            ->update($aliceModifiee)
            ->willReturn($aliceModifiee)
            ->shouldBeCalledOnce();

        $estAdmin = json_encode($aliceModifiee->getEstAdmin());
        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            false,
            'PUT',
            "/utilisateur/{$this->alice->getId()}",
            '',
            <<<EOT
{
    "genre": "{$aliceModifiee->getGenre()}",
    "identifiant": "{$aliceModifiee->getIdentifiant()}",
    "mdp": "{$aliceModifiee->getMdp()}",
    "nom": "{$aliceModifiee->getNom()}",
    "estAdmin": $estAdmin
}
EOT
        );

        $this->assertEquals('null', (string)$response->getBody());
    }

    public function testActionAdmin()
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
            ->setMdp($this->alice->getMdp())
            ->setEstAdmin(!$this->alice->getEstAdmin());
        $this->utilisateurRepositoryProphecy
            ->update($aliceModifiee)
            ->willReturn($aliceModifiee)
            ->shouldBeCalledOnce();

        $estAdmin = json_encode($aliceModifiee->getEstAdmin());
        $response = $this->handleAuthRequest(
            $this->bob->getId(),
            true,
            'PUT',
            "/utilisateur/{$this->alice->getId()}",
            '',
            <<<EOT
{
    "genre": "{$aliceModifiee->getGenre()}",
    "identifiant": "{$aliceModifiee->getIdentifiant()}",
    "nom": "{$aliceModifiee->getNom()}",
    "estAdmin": $estAdmin
}
EOT
        );

        $this->assertEquals('null', (string)$response->getBody());
    }

    public function testActionChangeEstAdmin()
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
            ->setMdp('nouveaumdpalice')
            ->setEstAdmin(!$this->alice->getEstAdmin());

        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->bob->getId(),
            true,
            'PUT',
            "/utilisateur/{$this->alice->getId()}",
            '',
            <<<EOT
{
    "genre": "{$aliceModifiee->getGenre()}",
    "identifiant": "{$aliceModifiee->getIdentifiant()}",
    "mdp": "{$aliceModifiee->getMdp()}",
    "nom": "{$aliceModifiee->getNom()}",
    "estAdmin": "{$aliceModifiee->getEstAdmin()}"
}
EOT
        );
    }

    public function testActionAdminChangeMdp()
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
            ->setMdp('nouveaumdpalice')
            ->setEstAdmin($this->alice->getEstAdmin());

        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->bob->getId(),
            true,
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
    }

    public function testActionAutreUtilisateur()
    {
        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->bob->getId(),
            false,
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
            false,
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
