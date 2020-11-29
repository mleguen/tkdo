<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Application\Service\MailerService;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class CreateParticipantOccasionAction extends Action
{
  protected $occasionRepository;
  protected $utilisateurRepository;
  private $mailerService;

  public function __construct(
    LoggerInterface $logger,
    OccasionRepository $occasionRepository,
    UtilisateurRepository $utilisateurRepository,
    MailerService $mailerService
  ) {
    parent::__construct($logger);
    $this->occasionRepository = $occasionRepository;
    $this->utilisateurRepository = $utilisateurRepository;
    $this->mailerService = $mailerService;
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

    $this->mailerService->envoieMailAjoutParticipant($this->request, $participant, $occasion);

    return $this->respondWithData(new SerializableUtilisateur($participant));
  }
}
