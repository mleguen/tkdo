<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use Slim\Exception\HttpBadRequestException;
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
            ->setTitre('Noel 2020');
        $occasion2 = (new DoctrineOccasion(2))
            ->setTitre('Noel 2019');
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
        "titre": "{$occasion1->getTitre()}"
    },
    {
        "id": {$occasion2->getId()},
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

    public function testActionIdParticipantManquant()
    {
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('idParticipant manquant');
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
