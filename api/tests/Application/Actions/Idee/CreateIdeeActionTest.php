<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurReference;
use Exception;
use Prophecy\Argument;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use Tests\Application\Actions\ActionTestCase;

class CreateIdeeActionTest extends ActionTestCase
{
    /**
     * @var DoctrineIdee
     */
    private $idee;

    public function setUp(): void
    {
        parent::setUp();
        $this->utilisateurRepositoryProphecy
            ->read($this->alice->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($this->alice->getId()));
        $this->utilisateurRepositoryProphecy
            ->read($this->bob->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($this->bob->getId()));
        
        $this->idee = (new DoctrineIdee(1));
        $this->idee
            ->setUtilisateur($this->alice)
            ->setDescription('un gauffrier')
            ->setAuteur($this->bob)
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

        $response = $this->handleAuthRequest(
            $this->idee->getAuteur()->getId(),
            false,
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

        $this->assertEquals('null', $response->getBody());
    }

    public function testActionPasLAuteur()
    {
        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->idee->getUtilisateur()->getId(),
            false,
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
    }

    public function testActionEchecCreation()
    {
        $this->ideeRepositoryProphecy
            ->create(Argument::cetera())
            ->willThrow(new Exception('erreur pendant create'))
            ->shouldBeCalledOnce();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('erreur pendant create');
        $this->handleAuthRequest(
            $this->idee->getAuteur()->getId(),
            false,
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
    }

    public function testActionNonAutorise()
    {
        $this->expectException(HttpUnauthorizedException::class);
        $this->handleRequest('POST', "/idee", '', <<<EOT
{
    "idUtilisateur": {$this->idee->getUtilisateur()->getId()},
    "description": "{$this->idee->getDescription()}",
    "idAuteur": {$this->idee->getAuteur()->getId()}
}
EOT
        );
    }
}
