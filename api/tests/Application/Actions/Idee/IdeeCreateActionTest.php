<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Prophecy\Argument;
use Tests\Application\Actions\ActionTestCase;

class IdeeCreateActionTest extends ActionTestCase
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
        $bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');

        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $this->utilisateurRepositoryProphecy
            ->read($bob->getId())
            ->willReturn($bob)
            ->shouldBeCalledOnce();

        $nouvelleIdee = (new DoctrineIdee(1));
        $nouvelleIdee
            ->setUtilisateur($this->alice)
            ->setDescription('un gauffrier')
            ->setAuteur($bob)
            ->setDateProposition(new \DateTime);
        $callTime = new \DateTime();
        $this->ideeRepositoryProphecy
            ->create(
                $nouvelleIdee->getUtilisateur(),
                $nouvelleIdee->getDescription(),
                $nouvelleIdee->getAuteur(),
                Argument::that(function (\DateTime $dateProposition) use ($callTime) {
                    $now = new \DateTime();
                    return ($dateProposition >= $callTime) && ($dateProposition <= $now);
                })
            )
            ->willReturn($nouvelleIdee)
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest('POST', "/utilisateur/{$this->alice->getId()}/idee", <<<EOT
{
    "description": "{$nouvelleIdee->getDescription()}",
    "idAuteur": {$nouvelleIdee->getAuteur()->getId()}
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

        $response = $this->handleAuthorizedRequest('POST', "/utilisateur/{$this->alice->getId()}/idee");

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
        $response = $this->handleRequest('POST', "/utilisateur/{$this->alice->getId()}/idee");

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
