<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Idee\IdeeInconnueException;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Infrastructure\Persistence\Idee\InMemoryIdeeReference;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Tests\Application\Actions\ActionTestCase;

class IdeeDeleteActionTest extends ActionTestCase
{
    /**
     * @var Utilisateur
     */
    private $alice;

    /**
     * @var Idee
     */
    private $idee;

    public function setUp()
    {
        parent::setup();
        $this->alice = (new DoctrineUtilisateur(1))
            ->setIdentifiant('alice@tkdo.org')
            ->setNom('Alice')
            ->setMdp('mdpalice');        
        $this->idee = new InMemoryIdeeReference(0);
    }

    public function testAction()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId(), true)
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

    public function testActionIdeeInconnue()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId(), true)
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
