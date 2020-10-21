<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurReference;
use Exception;
use Prophecy\Argument;
use Tests\Application\Actions\ActionTestCase;

class IdeeCreateActionTest extends ActionTestCase
{
    /**
     * @var DoctrineIdee
     */
    private $idee;

    public function setUp()
    {
        parent::setUp();
        $alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');
        $bob = (new DoctrineUtilisateur(2))
            ->setIdentifiant('bob@tkdo.org')
            ->setNom('Bob')
            ->setMdp('mdpbob');
        $this->utilisateurRepositoryProphecy
            ->read($alice->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($alice->getId()));
        $this->utilisateurRepositoryProphecy
            ->read($bob->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($bob->getId()));
        
        $this->idee = (new DoctrineIdee(1));
        $this->idee
            ->setUtilisateur($alice)
            ->setDescription('un gauffrier')
            ->setAuteur($bob)
            ->setDateProposition(new \DateTime);
    }

    public function testAction()
    {
        $callTime = new \DateTime();
        $this->ideeRepositoryProphecy
            ->create(
                new InMemoryUtilisateurReference($this->idee->getUtilisateur()->getId()),
                $this->idee->getDescription(),
                new InMemoryUtilisateurReference($this->idee->getAuteur()->getId()),
                Argument::that(function (\DateTime $dateProposition) use ($callTime) {
                    $now = new \DateTime();
                    return ($dateProposition >= $callTime) && ($dateProposition <= $now);
                })
            )
            ->willReturn($this->idee)
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'POST',
            "/idee",
            '',
            <<<EOT
{
    "idUtilisateur": {$this->idee->getUtilisateur()->getId()},
    "description": "{$this->idee->getDescription()}",
    "idAuteur": {$this->idee->getAuteur()->getId()}
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

    public function testActionEchecCreation()
    {
        $this->ideeRepositoryProphecy
            ->create(Argument::cetera())
            ->willThrow(new Exception('erreur pendant create'))
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'POST',
            "/idee",
            '',
            <<<EOT
{
    "idUtilisateur": {$this->idee->getUtilisateur()->getId()},
    "description": "{$this->idee->getDescription()}",
    "idAuteur": {$this->idee->getAuteur()->getId()}
}
EOT
        );

        $this->assertEqualsResponse(
            500,
            <<<'EOT'
{
    "type": "SERVER_ERROR",
    "description": "erreur pendant create"
}
EOT
            , $response
        );
    }

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest('POST', "/idee", '', <<<EOT
{
    "idUtilisateur": {$this->idee->getUtilisateur()->getId()},
    "description": "{$this->idee->getDescription()}",
    "idAuteur": {$this->idee->getAuteur()->getId()}
}
EOT
        );

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
