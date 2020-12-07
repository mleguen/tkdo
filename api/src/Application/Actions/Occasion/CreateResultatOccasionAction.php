<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Serializable\Resultat\SerializableResultat;
use App\Application\Service\DateService;
use App\Application\Service\MailerService;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Resultat\ResultatRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class CreateResultatOccasionAction extends OccasionAction
{
    protected $utilisateurRepository;
    protected $resultatRepository;
    private $mailerService;

    public function __construct(
        LoggerInterface $logger,
        OccasionRepository $occasionRepository,
        DateService $dateService,
        UtilisateurRepository $utilisateurRepository,
        ResultatRepository $resultatRepository,
        MailerService $mailerService
    ) {
        parent::__construct($logger, $occasionRepository, $dateService);
        $this->utilisateurRepository = $utilisateurRepository;
        $this->resultatRepository = $resultatRepository;
        $this->mailerService = $mailerService;
    }

    protected function action(): Response
    {
        $this->assertAuth();
        $this->assertUtilisateurAuthEstAdmin();
        $body = $this->getFormData();

        if (!isset($body['idQuiOffre'])) throw new HttpBadRequestException($this->request, "L'id du participant qui offre est obligatoire");
        if (!isset($body['idQuiRecoit'])) throw new HttpBadRequestException($this->request, "L'id du participant qui reçoit est obligatoire");

        $occasion = $this->occasionRepository->read((int) $this->resolveArg('idOccasion'));
        $quiOffre = $this->utilisateurRepository->read((int) $body['idQuiOffre']);
        $quiRecoit = $this->utilisateurRepository->read((int) $body['idQuiRecoit']);

        $resultat = $this->resultatRepository->create($occasion, $quiOffre, $quiRecoit);

        if (!$this->dateService->estPassee($occasion->getDate())) {
            $this->mailerService->envoieMailTirageFait($this->request, $quiOffre, $occasion);
        }

        return $this->respondWithData(new SerializableResultat($resultat));
    }
}
