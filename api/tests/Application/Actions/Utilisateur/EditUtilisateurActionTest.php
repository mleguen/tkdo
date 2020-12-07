<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Utilisateur;

use App\Domain\Utilisateur\PrefNotifIdees;
use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Prophecy\Argument;
use Slim\Exception\HttpBadRequestException;
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

        $nouveauMdp = 'nouveaumdpalice';
        /** @var DoctrineUtilisateur */
        $aliceModifiee = (new DoctrineUtilisateur($this->alice->getId()))
            ->setIdentifiant('alice2')
            ->setEmail('alice2@tkdo.org')
            ->setNom('Alice2')
            ->setGenre('M')
            ->setMdp(password_hash($nouveauMdp, PASSWORD_DEFAULT))
            ->setEstAdmin($this->alice->getEstAdmin())
            ->setPrefNotifIdees($this->alice->getPrefNotifIdees() === PrefNotifIdees::Aucune
                ? PrefNotifIdees::Instantanee
                : PrefNotifIdees::Aucune
            );
        $testCase = $this;
        $this->utilisateurRepositoryProphecy
            ->update(Argument::cetera())
            ->will(function($args) use($testCase, $aliceModifiee, $nouveauMdp) {
                /** @var Utilisateur */
                $u = $args[0];
                $testCase->assertEquals($aliceModifiee->getIdentifiant(), $u->getIdentifiant());
                $testCase->assertEquals($aliceModifiee->getNom(), $u->getNom());
                $testCase->assertEquals($aliceModifiee->getGenre(), $u->getGenre());
                $testCase->assertEquals(true, password_verify($nouveauMdp, $u->getMdp()));
                $testCase->assertEquals($aliceModifiee->getEstAdmin(), $u->getEstAdmin());
                return $aliceModifiee;
            })
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
    "email": "{$aliceModifiee->getEmail()}",
    "mdp": "{$nouveauMdp}",
    "nom": "{$aliceModifiee->getNom()}",
    "estAdmin": $estAdmin
}

EOT
        );

        $estAdmin = json_encode($aliceModifiee->getEstAdmin());
        $json = <<<EOT
{
    "email": "{$aliceModifiee->getEmail()}",
    "estAdmin": $estAdmin,
    "genre": "{$aliceModifiee->getGenre()}",
    "id": {$aliceModifiee->getId()},
    "identifiant": "{$aliceModifiee->getIdentifiant()}",
    "nom": "{$aliceModifiee->getNom()}",
    "prefNotifIdees": "{$aliceModifiee->getPrefNotifIdees()}"
}

EOT;
        $this->assertEquals($json, (string)$response->getBody());
    }

    public function testActionAdmin()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        
        $mdp = $this->mdpalice;
        /** @var DoctrineUtilisateur */
        $aliceModifiee = (new DoctrineUtilisateur($this->alice->getId()))
            ->setIdentifiant('alice2')
            ->setEmail('alice2@tkdo.org')
            ->setNom('Alice2')
            ->setGenre('M')
            ->setMdp(password_hash($mdp, PASSWORD_DEFAULT))
            ->setEstAdmin(!$this->alice->getEstAdmin())
            ->setPrefNotifIdees($this->alice->getPrefNotifIdees() === PrefNotifIdees::Aucune
                ? PrefNotifIdees::Instantanee
                : PrefNotifIdees::Aucune
            );
        $testCase = $this;
        $this->utilisateurRepositoryProphecy
            ->update(Argument::cetera())
            ->will(function ($args) use ($testCase, $aliceModifiee, $mdp) {
                /** @var Utilisateur */
                $u = $args[0];
                $testCase->assertEquals($aliceModifiee->getIdentifiant(), $u->getIdentifiant());
                $testCase->assertEquals($aliceModifiee->getNom(), $u->getNom());
                $testCase->assertEquals($aliceModifiee->getGenre(), $u->getGenre());
                $testCase->assertEquals(true, password_verify($mdp, $u->getMdp()));
                $testCase->assertEquals($aliceModifiee->getEstAdmin(), $u->getEstAdmin());
                return $aliceModifiee;
            })
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
    "email": "{$aliceModifiee->getEmail()}",
    "nom": "{$aliceModifiee->getNom()}",
    "estAdmin": $estAdmin
}

EOT
        );

        $estAdmin = json_encode($aliceModifiee->getEstAdmin());
        $json = <<<EOT
{
    "email": "{$aliceModifiee->getEmail()}",
    "estAdmin": $estAdmin,
    "genre": "{$aliceModifiee->getGenre()}",
    "id": {$aliceModifiee->getId()},
    "identifiant": "{$aliceModifiee->getIdentifiant()}",
    "nom": "{$aliceModifiee->getNom()}",
    "prefNotifIdees": "{$aliceModifiee->getPrefNotifIdees()}"
}

EOT;
        $this->assertEquals($json, (string)$response->getBody());
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
            ->setEstAdmin(!$this->alice->getEstAdmin());

        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->alice->getId(),
            false,
            'PUT',
            "/utilisateur/{$this->alice->getId()}",
            '',
            <<<EOT
{
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
            ->setMdp('nouveaumdpalice');

        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->bob->getId(),
            true,
            'PUT',
            "/utilisateur/{$this->alice->getId()}",
            '',
            <<<EOT
{
    "mdp": "{$aliceModifiee->getMdp()}"
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

    public function testActionEmailInvalide()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        /** @var DoctrineUtilisateur */
        $aliceModifiee = (new DoctrineUtilisateur($this->alice->getId()))
            ->setEmail('alice.tkdo.org');

        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage("{$aliceModifiee->getEmail()} n'est pas un email valide");
        $this->handleAuthRequest(
            $this->alice->getId(),
            $this->alice->getEstAdmin(),
            'PUT',
            "/utilisateur/{$this->alice->getId()}",
            '',
            <<<EOT
{
    "email": "{$aliceModifiee->getEmail()}"
}

EOT
        );
    }
}
