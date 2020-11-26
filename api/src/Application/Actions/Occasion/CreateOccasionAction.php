<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Serializable\Occasion\SerializableOccasion;
use App\Domain\Occasion\OccasionRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class CreateOccasionAction extends Action
{
  protected $occasionRepository;
  
  public function __construct(
    LoggerInterface $logger,
    OccasionRepository $occasionRepository
  ) {
    parent::__construct($logger);
    $this->occasionRepository = $occasionRepository;
  }

  protected function action(): Response
  {
    $this->assertAuth();
    $this->assertUtilisateurAuthEstAdmin();
    $body = $this->getFormData();

    if (!isset($body['titre'])) throw new HttpBadRequestException($this->request, "Le titre est obligatoire");

    $occasion = $this->occasionRepository->create($body['titre']);

    return $this->respondWithData(new SerializableOccasion($occasion));
  }
}
