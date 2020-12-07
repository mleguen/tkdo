<?php
declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Serializable\Occasion\SerializableOccasion;
use Psr\Http\Message\ResponseInterface as Response;

class ListOccasionAction extends OccasionAction
{
    protected function action(): Response
    {
        $this->assertAuth();

        $queryParams = $this->request->getQueryParams();
        if (isset($queryParams['idParticipant'])) {
            $idParticipant = (int) $queryParams['idParticipant'];
            $this->assertUtilisateurAuthEst([$idParticipant]);
            $occasions = $this->occasionRepository->readByParticipant($idParticipant);
        }
        else {
            $this->assertUtilisateurAuthEstAdmin();
            $occasions = $this->occasionRepository->readAll();
        }

        return $this->respondWithData(array_map(function ($occasion) {
            return new SerializableOccasion($occasion, $this->dateService);
        }, $occasions));
    }
}
