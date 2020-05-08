<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Idee\IdeeInconnueException;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Reference\InMemoryReference;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class IdeeDeleteActionTest extends ActionTestCase
{
    /**
     * @var Utilisateur
     */
    private $alice;

    /**
     * @var Reference
     */
    private $idee;

    public function setUp()
    {
        parent::setup();
        $this->alice = new InMemoryUtilisateur(0, 'alice@tkdo.org', 'Alice', 'Alice');        
        $this->idee = new InMemoryReference(0);
    }

    public function testAction()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $this->ideeRepositoryProphecy
            ->getReference($this->idee->getId())
            ->willReturn($this->idee)
            ->shouldBeCalledOnce();

        $this->ideeRepositoryProphecy
            ->delete($this->idee)
            ->willReturn()
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            'DELETE',
            "/utilisateur/{$this->alice->getId()}/idee/{$this->idee->getId()}"
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

        $response = $this->handleAuthorizedRequest(
            'DELETE',
            "/utilisateur/{$this->alice->getId()}/idee/{$this->idee->getId()}"
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

    public function testActionIdeeInconnue()
    {
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId())
            ->willReturn($this->alice)
            ->shouldBeCalledOnce();

        $this->ideeRepositoryProphecy
            ->getReference($this->idee->getId())
            ->willReturn($this->idee)
            ->shouldBeCalledOnce();

        $this->ideeRepositoryProphecy
            ->delete($this->idee)
            ->willThrow(new IdeeInconnueException())
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            'DELETE',
            "/utilisateur/{$this->alice->getId()}/idee/{$this->idee->getId()}"
        );

        $this->assertEqualsResponse(
            404,
            <<<'EOT'
{
    "type": "RESOURCE_NOT_FOUND",
    "description": "id\u00e9e inconnue"
}
EOT
            , $response
        );
    }

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest(
            'DELETE',
            "/utilisateur/{$this->alice->getId()}/idee/{$this->idee->getId()}"
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
