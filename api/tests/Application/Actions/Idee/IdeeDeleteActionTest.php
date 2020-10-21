<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Idee\IdeeInconnueException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Exception;
use Tests\Application\Actions\ActionTestCase;

class IdeeDeleteActionTest extends ActionTestCase
{
    /**
     * @var Idee
     */
    private $idee;

    public function setUp()
    {
        parent::setup();

        $this->idee = (new DoctrineIdee(1));
        $this->idee
            ->setAuteur(new DoctrineUtilisateur(1))
            ->setUtilisateur(new DoctrineUtilisateur(2));
    }

    public function testAction()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willReturn($this->idee)
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->delete($this->idee)
            ->willReturn()
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );

        $this->assertEqualsResponse(
            200,
            <<<'EOT'
null
EOT
            , $response
        );
    }

    public function testActionPasLAuteur()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willReturn($this->idee)
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getUtilisateur()->getId(),
            'DELETE',
            "/idee/{$this->idee->getId()}"
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

    public function testActionIdeeInconnue()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willThrow(new IdeeInconnueException())
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'DELETE',
            "/idee/{$this->idee->getId()}"
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


    public function testActionEchecRead()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willThrow(new Exception('échec de read'))
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );

        $this->assertEqualsResponse(
            500,
            <<<'EOT'
{
    "type": "SERVER_ERROR",
    "description": "\u00e9chec de read"
}
EOT
            , $response
        );
    }

    public function testActionEchecDelete()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willReturn($this->idee)
            ->shouldBeCalledOnce();
        $this->ideeRepositoryProphecy
            ->delete($this->idee)
            ->willThrow(new Exception('échec de delete'))
            ->shouldBeCalledOnce();

        $response = $this->handleAuthorizedRequest(
            $this->idee->getAuteur()->getId(),
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );

        $this->assertEqualsResponse(
            500,
            <<<'EOT'
{
    "type": "SERVER_ERROR",
    "description": "\u00e9chec de delete"
}
EOT
            , $response
        );
    }

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest(
            'DELETE',
            "/idee/{$this->idee->getId()}"
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
