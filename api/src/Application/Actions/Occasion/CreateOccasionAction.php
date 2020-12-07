<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Serializable\Occasion\SerializableOccasion;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class CreateOccasionAction extends OccasionAction
{
  protected function action(): Response
  {
    $this->assertAuth();
    $this->assertUtilisateurAuthEstAdmin();
    $body = $this->getFormData();

    if (!isset($body['date'])) throw new HttpBadRequestException($this->request, "La date est obligatoire");
    $date = $this->dateService->decodeDate($body['date']);
    if (!$date) throw new HttpBadRequestException($this->request, "Format de date incorrect : {$body['date']}");
    if (!isset($body['titre'])) throw new HttpBadRequestException($this->request, "Le titre est obligatoire");

    $occasion = $this->occasionRepository->create($date, $body['titre']);

    return $this->respondWithData(new SerializableOccasion($occasion, $this->dateService));
  }
}
