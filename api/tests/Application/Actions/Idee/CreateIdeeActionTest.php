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
            ->read($this->alice->getId())
            ->willReturn($this->alice);
        $this->utilisateurRepositoryProphecy
            ->read($this->bob->getId(), true)
            ->willReturn(new InMemoryUtilisateurReference($this->bob->getId()));
        
        $this->idee = (new DoctrineIdee(1));
        $this->idee
            ->setUtilisateur($this->alice)
            ->setDescription('un gauffrier')
            ->setAuteur($this->bob)
            ->setDateProposition(new \DateTime);

        $this->utilisateurRepositoryProphecy
            ->readAllByNotifInstantaneePourIdees($this->idee->getUtilisateur()->getId(), Argument::cetera())
            ->willReturn([]);
    }

    /**
     * @dataProvider providerAction
     */
    public function testAction(bool $estAdmin)
    {
        $testCase = $this;
        $callTime = new \DateTime();
        /** @var \DateTime */
        $dateProposition = null;
        $this->ideeRepositoryProphecy
            ->create(Argument::cetera())
            ->will(function ($args) use ($testCase, $callTime, &$dateProposition) {
                $testCase->assertEquals($testCase->idee->getUtilisateur(), $args[0]);
                $testCase->assertEquals($testCase->idee->getDescription(), $args[1]);
                $testCase->assertEquals($testCase->idee->getAuteur()->getId(), $args[2]->getId());

                $dateProposition = $args[3];
                $testCase->assertGreaterThanOrEqual($callTime, $dateProposition);
                $testCase->assertLessThanOrEqual(new \DateTime(), $dateProposition);
                return $testCase->idee;
            })
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $estAdmin ? $this->charlie->getId() : $this->idee->getAuteur()->getId(),
            $estAdmin,
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

        $this->assertNotNull($dateProposition);
        $json = <<<EOT
{
    "id": {$this->idee->getId()},
    "description": "{$this->idee->getDescription()}",
    "auteur": {
        "genre": "{$this->idee->getAuteur()->getGenre()}",
        "id": {$this->idee->getAuteur()->getId()},
        "nom": "{$this->idee->getAuteur()->getNom()}"
    },
    "dateProposition": "{$dateProposition->format(\DateTimeInterface::ISO8601)}"
}

EOT;
        $this->assertEquals($json, (string)$response->getBody());
    }

    public function providerAction()
    {
        return [
            [ // L'auteur de l'idée
                'estAdmin' => false,
            ],
            [ // Un administrateur
                'estAdmin' => true,
            ],
        ];
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
