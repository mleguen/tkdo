<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Domain\Occasion\AucuneOccasionException;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use App\Infrastructure\Persistence\ResultatTirage\DoctrineResultatTirage;
use Tests\Application\Actions\ActionTestCase;

class OccasionReadActionTest extends ActionTestCase
{
    public function testAction()
    {
        $occasion = (new DoctrineOccasion(1))
            ->setTitre('Noel 2020')
            ->setParticipants([$this->alice, $this->bob]);
        $this->occasionRepositoryProphecy
            ->readLast()
            ->willReturn($occasion)
            ->shouldBeCalledOnce();

        $resultatTirage = (new DoctrineResultatTirage($occasion, $this->alice))
            ->setQuiRecoit($this->bob);
        $this->resultatTirageRepositoryProphecy
            ->readByOccasion($occasion)
            ->willReturn([$resultatTirage])
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            'GET',
            '/occasion'
        );

        $this->assertEqualsResponse(
            200,
            <<<EOT
{
    "id": {$occasion->getId()},
    "titre": "{$occasion->getTitre()}",
    "participants": [
        {
            "id": {$this->alice->getId()},
            "identifiant": "{$this->alice->getIdentifiant()}",
            "nom": "{$this->alice->getNom()}"
        },
        {
            "id": {$this->bob->getId()},
            "identifiant": "{$this->bob->getIdentifiant()}",
            "nom": "{$this->bob->getNom()}"
        }
    ],
    "resultatsTirage": [
        {
            "idQuiOffre": {$resultatTirage->getQuiOffre()->getId()},
            "idQuiRecoit": {$resultatTirage->getQuiRecoit()->getId()}
        }
    ]
}
EOT
            , $response
        );
    }

    public function testActionPasDOccasion()
    {
        $this->occasionRepositoryProphecy
            ->readLast()
            ->willThrow(new AucuneOccasionException())
            ->shouldBeCalledOnce();

        $response = $this->handleAuthRequest(
            $this->alice->getId(),
            'GET',
            '/occasion'
        );

        $this->assertEqualsResponse(
            404,
            <<<'EOT'
{
    "type": "RESOURCE_NOT_FOUND",
    "description": "aucune occasion"
}
EOT
            , $response
        );
    }

    public function testActionNonAutorise()
    {
        $response = $this->handleRequest('GET', '/occasion');

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
