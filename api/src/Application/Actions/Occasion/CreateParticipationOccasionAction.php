<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class CreateParticipationOccasionAction extends Action
{
  protected $occasionRepository;
  protected $utilisateurRepository;

  public function __construct(
    LoggerInterface $logger,
    OccasionRepository $occasionRepository,
    UtilisateurRepository $utilisateurRepository
  ) {
    parent::__construct($logger);
    $this->occasionRepository = $occasionRepository;
    $this->utilisateurRepository = $utilisateurRepository;
  }

  protected function action(): Response
  {
    $this->assertAuth();
    $this->assertUtilisateurAuthEstAdmin();
    $body = $this->getFormData();

    if (!isset($body['idParticipant'])) throw new HttpBadRequestException($this->request, "L'id du participant est obligatoire");

    $occasion = $this->occasionRepository->read((int) $this->resolveArg('idOccasion'));
    $participant = $this->utilisateurRepository->read((int) $body['idParticipant']);
    $occasion->addParticipant($participant);

    $occasion = $this->occasionRepository->update($occasion);
    return $this->respondWithData(new SerializableUtilisateur($participant));
  }
}
