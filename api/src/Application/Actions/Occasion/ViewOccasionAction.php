<?php
declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Serializable\Occasion\SerializableOccasionDetaillee;
use App\Application\Service\DateService;
use App\Domain\Occasion\OccasionRepository;
use App\Domain\Resultat\ResultatRepository;
use App\Domain\Utilisateur\Utilisateur;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ViewOccasionAction extends OccasionAction
{
    protected $resultatRepository;

    public function __construct(
        LoggerInterface $logger,
        OccasionRepository $occasionRepository,
        DateService $dateService,
        ResultatRepository $resultatRepository
    )
    {
        parent::__construct($logger, $occasionRepository, $dateService);
        $this->resultatRepository = $resultatRepository;
    }

    protected function action(): Response
    {
        $this->assertAuth();
        $occasion = $this->occasionRepository->read((int) $this->resolveArg('idOccasion'));

        $this->assertUtilisateurAuthEst(
            array_map(
                function (Utilisateur $u) {
                    return $u->getId();
                },
                $occasion->getParticipants()
            ),
            "L'utilisateur authentifié ne participe pas à l'occasion et n'est pas admin"
        );

        return $this->respondWithData(new SerializableOccasionDetaillee(
            $occasion,
            $this->dateService,
            $this->resultatRepository->readByOccasion($occasion)
        ));
    }
}
