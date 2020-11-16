<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Idee\IdeeNotFoundException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateur;
use Exception;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Tests\Application\Actions\ActionTestCase;

class DeleteIdeeActionTest extends ActionTestCase
{
    /**
     * @var Idee
     */
    private $idee;

    public function setUp(): void
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

        $response = $this->handleAuthRequest(
            $this->idee->getAuteur()->getId(),
            false,
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );

        $this->assertEquals("null\n", $response->getBody());
    }

    public function testActionPasLAuteur()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willReturn($this->idee)
            ->shouldBeCalledOnce();

        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->idee->getUtilisateur()->getId(),
            false,
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );
    }

    public function testActionIdeeInconnue()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willThrow(new IdeeNotFoundException())
            ->shouldBeCalledOnce();

        $this->expectException(HttpNotFoundException::class);
        $this->expectExceptionMessage('idée inconnue');
        $this->handleAuthRequest(
            $this->idee->getAuteur()->getId(),
            false,
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );
    }


    public function testActionEchecRead()
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willThrow(new Exception('échec de read'))
            ->shouldBeCalledOnce();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('échec de read');
        $this->handleAuthRequest(
            $this->idee->getAuteur()->getId(),
            false,
            'DELETE',
            "/idee/{$this->idee->getId()}"
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('échec de delete');
        $this->handleAuthRequest(
            $this->idee->getAuteur()->getId(),
            false,
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );
    }

    public function testActionNonAutorise()
    {
        $this->expectException(HttpUnauthorizedException::class);
        $this->handleRequest(
            'DELETE',
            "/idee/{$this->idee->getId()}"
        );
    }
}
