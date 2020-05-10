<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class IdeeReadAllActionTest extends ActionTestCase
{
    /**
     * @var Utilisateur
     */
    private $alice;

    public function setUp()
    {
        parent::setup();
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
    }

    public function testAction()
    {
        $bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');

        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $dateProposition = '2020-04-19T00:00:00+0000';
        $idee = (new DoctrineIdee(1))
            ->setUtilisateur($this->alice)
            ->setDescription('un gauffrier')
            ->setAuteur($bob)
            ->setDateProposition(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, $dateProposition));
        $this->ideeRepositoryProphecy
            ->readByUtilisateur($this->alice)
            ->willReturn([$idee])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest('GET', "/utilisateur/{$this->alice->getId()}/idee");

        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "utilisateur": {
        "id": {$this->alice->getId()},
        "identifiant": "{$this->alice->getIdentifiant()}",
        "nom": "{$this->alice->getNom()}"
    },
    "idees": [
        {
            "id": {$idee->getId()},
            "description": "{$idee->getDescription()}",
            "auteur": {
                "id": {$idee->getAuteur()->getId()},
                "identifiant": "{$idee->getAuteur()->getIdentifiant()}",
                "nom": "{$idee->getAuteur()->getNom()}"
            },
            "dateProposition": "$dateProposition"
        }
    ]
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

        $response = $this->handleAuthorizedRequest('GET', "/utilisateur/{$this->alice->getId()}/idee");

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
        $response = $this->handleRequest('GET', "/utilisateur/{$this->alice->getId()}/idee");

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
