<?php

declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Serializable\Resultat\SerializableResultat;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Resultat\ResultatRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class CreateResultatOccasionAction extends Action
{
    protected $occasionRepository;
    protected $utilisateurRepository;
    protected $resultatRepository;

    public function __construct(
        LoggerInterface $logger,
        OccasionRepository $occasionRepository,
        UtilisateurRepository $utilisateurRepository,
        ResultatRepository $resultatRepository
    ) {
        parent::__construct($logger);
        $this->occasionRepository = $occasionRepository;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->resultatRepository = $resultatRepository;
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
        return $this->respondWithData(new SerializableResultat($resultat));
    }
}
