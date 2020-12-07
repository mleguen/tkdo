<?php
declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Serializable\Occasion\SerializableOccasion;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class EditOccasionAction extends OccasionAction
{
    protected function action(): Response
    {
        $this->assertAuth();
        $this->assertUtilisateurAuthEstAdmin();
        
        $occasion = $this->occasionRepository->read((int) $this->resolveArg('idOccasion'));
        $body = $this->getFormData();
        if (isset($body['date'])) {
            $date = $this->dateService->decodeDate($body['date']);
            if (!$date) throw new HttpBadRequestException($this->request, "Format de date incorrect : {$body['date']}");
            $occasion->setDate($date);
        }
        if (isset($body['titre'])) $occasion->setTitre($body['titre']);

        $occasion = $this->occasionRepository->update($occasion);
        return $this->respondWithData(new SerializableOccasion($occasion, $this->dateService));
    }
}
