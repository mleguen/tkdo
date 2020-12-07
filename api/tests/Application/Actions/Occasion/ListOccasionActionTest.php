<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use DateTimeInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use Tests\Application\Actions\ActionTestCase;

class ListOccasionActionTest extends ActionTestCase
{
    /**
     * @dataProvider providerTestAction 
     */
    public function testAction($estAdmin)
    {
        $occasion1 = (new DoctrineOccasion(1))
            ->setDate(new \DateTime('tomorrow'))
            ->setTitre('Demain');
        $occasion2 = (new DoctrineOccasion(2))
            ->setDate(new \DateTime('yesterday'))
            ->setTitre('Hier');
        $this->occasionRepositoryProphecy
            ->readByParticipant($this->bob->getId())
            ->willReturn([$occasion1, $occasion2])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            ($estAdmin ? $this->alice : $this->bob)->getId(),
            $estAdmin,
            'GET',
            '/occasion',
            "idParticipant={$this->bob->getId()}"
        );

        $json = <<<EOT
[
    {
        "id": {$occasion1->getId()},
        "date": "{$occasion1->getDate()->format(DateTimeInterface::W3C)}",
        "titre": "{$occasion1->getTitre()}"
    },
    {
        "id": {$occasion2->getId()},
        "date": "{$occasion2->getDate()->format(DateTimeInterface::W3C)}",
        "titre": "{$occasion2->getTitre()}"
    }
]

EOT;
        $this->assertEquals($json, (string)$response->getBody());
    }

    public function providerTestAction() {
        return [
            [false],
            [true]
        ];
    }

    public function testActionPasDOccasion()
    {
        $this->occasionRepositoryProphecy
            ->readByParticipant($this->alice->getId())
            ->willReturn([])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            false,
            'GET',
            '/occasion',
            "idParticipant={$this->alice->getId()}"
        );

        $json = <<<EOT
[]

EOT;
        $this->assertEquals($json, (string)$response->getBody());
    }

    public function testActionSansIdParticipantAdmin()
    {
        $occasion1 = (new DoctrineOccasion(1))
            ->setDate(new \DateTime('tomorrow'))
            ->setTitre('Demain');
        $occasion2 = (new DoctrineOccasion(2))
            ->setDate(new \DateTime('yesterday'))
            ->setTitre('Hier');
        $this->occasionRepositoryProphecy
            ->readAll()
            ->willReturn([$occasion1, $occasion2])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            true,
            'GET',
            '/occasion'
        );

        $json = <<<EOT
[
    {
        "id": {$occasion1->getId()},
        "date": "{$occasion1->getDate()->format(DateTimeInterface::W3C)}",
        "titre": "{$occasion1->getTitre()}"
    },
    {
        "id": {$occasion2->getId()},
        "date": "{$occasion2->getDate()->format(DateTimeInterface::W3C)}",
        "titre": "{$occasion2->getTitre()}"
    }
]

EOT;
        $this->assertEquals($json, (string)$response->getBody());
    }

    public function testActionMauvaisIdParticipant()
    {
        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->bob->getId(),
            false,
            'GET',
            '/occasion',
            "idParticipant={$this->alice->getId()}"
        );
    }

    public function testActionSansIdParticipantPasAdmin()
    {
        $this->expectException(HttpForbiddenException::class);
        $this->handleAuthRequest(
            $this->alice->getId(),
            false,
            'GET',
            '/occasion'
        );
    }

    public function testActionNonAutorise()
    {
        $this->expectException(HttpUnauthorizedException::class);
        $this->handleRequest('GET', '/occasion');
    }
}
