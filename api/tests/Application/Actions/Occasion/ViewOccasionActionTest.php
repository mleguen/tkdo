<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Domain\Occasion\OccasionNotFoundException;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use App\Infrastructure\Persistence\Resultat\DoctrineResultat;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Tests\Application\Actions\ActionTestCase;

class ViewOccasionActionTest extends ActionTestCase
{
    public function testAction()
    {
        $occasion = (new DoctrineOccasion(1))
            ->setTitre('Noel 2020')
            ->setParticipants([$this->alice, $this->bob]);
        $this->occasionRepositoryProphecy
            ->readLastByParticipant($this->alice->getId())
            ->willReturn($occasion)
            ->shouldBeCalledOnce();

        $resultat = (new DoctrineResultat($occasion, $this->alice))
            ->setQuiRecoit($this->bob);
        $this->resultatRepositoryProphecy
            ->readByOccasion($occasion)
            ->willReturn([$resultat])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            'GET',
            '/occasion'
        );

        $json = <<<EOT
{
    "id": {$occasion->getId()},
    "titre": "{$occasion->getTitre()}",
    "participants": [
        {
            "genre": "{$this->alice->getGenre()}",
            "id": {$this->alice->getId()},
            "identifiant": "{$this->alice->getIdentifiant()}",
            "nom": "{$this->alice->getNom()}"
        },
        {
            "genre": "{$this->bob->getGenre()}",
            "id": {$this->bob->getId()},
            "identifiant": "{$this->bob->getIdentifiant()}",
            "nom": "{$this->bob->getNom()}"
        }
    ],
    "resultats": [
        {
            "idQuiOffre": {$resultat->getQuiOffre()->getId()},
            "idQuiRecoit": {$resultat->getQuiRecoit()->getId()}
        }
    ]
}
EOT;
        $this->assertEquals($json, (string)$response->getBody());
    }

    public function testActionPasDOccasion()
    {
        $this->occasionRepositoryProphecy
            ->readLastByParticipant($this->alice->getId())
            ->willThrow(new OccasionNotFoundException())
            ->shouldBeCalledOnce();

        $this->expectException(HttpNotFoundException::class);
        $this->expectExceptionMessage('aucune occasion');
        $this->handleAuthRequest(
            $this->alice->getId(),
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
