<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Dto\CreateResultatInput;
use App\Entity\Occasion;
use App\Entity\Resultat;
use App\Repository\UtilisateurRepository;
use App\Repository\IdeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OccasionResultatProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $occasionProvider,
        private UtilisateurRepository $utilisateurRepository,
        private IdeeRepository $ideeRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Resultat
    {
        // Get the occasion using the standard provider (check entity exists first)
        $occasion = $this->occasionProvider->provide($operation, $uriVariables, $context);
        
        if (!$occasion instanceof Occasion) {
            throw new NotFoundHttpException('Occasion non trouvée');
        }

        /** @var CreateResultatInput $data */
        if (!$data instanceof CreateResultatInput) {
            throw new BadRequestHttpException('Invalid input data');
        }

        $donneur = $this->utilisateurRepository->find($data->donneurId);
        $receveur = $this->utilisateurRepository->find($data->receveurId);

        if (!$donneur || !$receveur) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $idee = null;
        if ($data->ideeId) {
            $idee = $this->ideeRepository->find($data->ideeId);
        }

        $resultat = new Resultat();
        $resultat->setOccasion($occasion);
        $resultat->setDonneur($donneur);
        $resultat->setReceveur($receveur);
        $resultat->setIdee($idee);

        $this->entityManager->persist($resultat);
        $this->entityManager->flush();

        return $resultat;
    }
}
