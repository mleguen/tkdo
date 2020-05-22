<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurReference;
use Prophecy\Argument;
use Tests\Application\Actions\ActionTestCase;

class IdeeCreateActionTest extends ActionTestCase
{
    /**
     * @var Utilisateur
     */
    private $alice;

    /**
     * @var Utilisateur
     */
    private $bob;

    /**
     * @var DoctrineIdee
     */
    private $nouvelleIdee;

    public function setUp()
    {
        parent::setUp();
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $this->bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');
        
        $this->nouvelleIdee = (new DoctrineIdee(1));
        $this->nouvelleIdee
            ->setUtilisateur($this->alice)
            ->setDescription('un gauffrier')
            ->setAuteur($this->bob)
            ->setDateProposition(new \DateTime);
    }

    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($this->alice->getId()))
            ->shouldBeCalledOnce();

        $this->utilisateurRepositoryProphecy
            ->read($this->bob->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($this->bob->getId()))
            ->shouldBeCalledOnce();

        $callTime = new \DateTime();
        $this->ideeRepositoryProphecy
            ->create(
                new InMemoryUtilisateurReference($this->nouvelleIdee->getUtilisateur()->getId()),
                $this->nouvelleIdee->getDescription(),
                new InMemoryUtilisateurReference($this->nouvelleIdee->getAuteur()->getId()),
                Argument::that(function (\DateTime $dateProposition) use ($callTime) {
                    $now = new \DateTime();
                    return ($dateProposition >= $callTime) && ($dateProposition <= $now);
                })
            )
            ->willReturn($this->nouvelleIdee)
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest('POST', "/utilisateur/{$this->alice->getId()}/idee", <<<EOT
{
    "description": "{$this->nouvelleIdee->getDescription()}",
    "idAuteur": {$this->nouvelleIdee->getAuteur()->getId()}
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
            ->read($this->alice->getId(), true)
            ->willThrow(new UtilisateurInconnuException())
            ->shouldBeCalledOnce();

        $this->utilisateurRepositoryProphecy
            ->read($this->bob->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($this->bob->getId()));

        $response = $this->handleAuthorizedRequest('POST', "/utilisateur/{$this->alice->getId()}/idee", <<<EOT
{
    "description": "{$this->nouvelleIdee->getDescription()}",
    "idAuteur": {$this->nouvelleIdee->getAuteur()->getId()}
}
EOT
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
