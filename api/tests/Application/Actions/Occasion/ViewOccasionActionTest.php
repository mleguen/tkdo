<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Occasion;

use App\Domain\Occasion\OccasionNotFoundException;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use App\Infrastructure\Persistence\Resultat\DoctrineResultat;
use Tests\Application\Actions\ActionTestCase;

class ViewOccasionActionTest extends ActionTestCase
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
    "resultats": [
        {
            "idQuiOffre": {$resultat->getQuiOffre()->getId()},
            "idQuiRecoit": {$resultat->getQuiRecoit()->getId()}
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
            ->willThrow(new OccasionNotFoundException())
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
