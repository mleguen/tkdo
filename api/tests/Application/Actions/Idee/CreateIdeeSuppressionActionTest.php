<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeNotFoundException;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use DateTime;
use DateTimeInterface;
use Prophecy\Argument;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Tests\Application\Actions\ActionTestCase;

class CreateIdeeSuppressionActionTest extends ActionTestCase
{
    /** @var Idee */
    private $idee;

    public function setUp(): void
    {
        parent::setup();
        $this->utilisateurRepositoryProphecy
            ->readAllByNotifInstantaneePourIdees($this->alice->getId(), Argument::cetera())
            ->willReturn([]);

        $this->idee = (new DoctrineIdee(1))
            ->setUtilisateur($this->alice)
            ->setDescription('un gauffrier')
            ->setAuteur($this->bob)
            ->setDateProposition(new DateTime());
    }

    /**
     * @dataProvider providerAction
     */
    public function testAction(bool $estAdmin)
    {
        $this->ideeRepositoryProphecy
            ->read($this->idee->getId())
            ->willReturn($this->idee)
            ->shouldBeCalledOnce();

        $testCase = $this;
        $callTime = new DateTime();
        /** @var DateTime */
        $dateSuppression = null;
        $this->ideeRepositoryProphecy
            ->update(Argument::cetera())
            ->will(function ($args) use ($testCase, $callTime, &$dateSuppression) {
                /** @var Idee */
                $ideeSupprimee = $args[0];
                $testCase->assertEquals($testCase->idee->getUtilisateur(), $ideeSupprimee->getUtilisateur());
                $testCase->assertEquals($testCase->idee->getDescription(), $ideeSupprimee->getDescription());
                $testCase->assertEquals($testCase->idee->getAuteur(), $ideeSupprimee->getAuteur());
                $testCase->assertEquals($testCase->idee->getDateProposition(), $ideeSupprimee->getDateProposition());
                $testCase->assertEquals(true, $ideeSupprimee->hasDateSuppression());

                $dateSuppression = $ideeSupprimee->getDateSuppression();
                $testCase->assertGreaterThanOrEqual($callTime, $dateSuppression);
                $testCase->assertLessThanOrEqual(new DateTime(), $dateSuppression);
                return $ideeSupprimee;
            })
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $estAdmin ? $this->charlie->getId() : $this->idee->getAuteur()->getId(),
            $estAdmin,
            'POST',
            "/idee/{$this->idee->getId()}/suppression"
        );

        $this->assertNotNull($dateSuppression);
        $json = <<<EOT
{
    "id": {$this->idee->getId()},
    "description": "{$this->idee->getDescription()}",
    "auteur": {
        "genre": "{$this->idee->getAuteur()->getGenre()}",
        "id": {$this->idee->getAuteur()->getId()},
        "nom": "{$this->idee->getAuteur()->getNom()}"
    },
    "dateProposition": "{$this->idee->getDateProposition()->format(DateTimeInterface::ISO8601)}",
    "dateSuppression": "{$dateSuppression->format(DateTimeInterface::ISO8601)}"
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
            'POST',
            "/idee/{$this->idee->getId()}/suppression"
        );
    }

    public function testActionNonAutorise()
    {
        $this->expectException(HttpUnauthorizedException::class);
        $this->handleRequest(
            'POST',
            "/idee/{$this->idee->getId()}/suppression"
        );
    }
}
