<?php
declare(strict_types=1);

namespace App\Application\Actions\Occasion;

use App\Application\Actions\Action;
use App\Application\Serializable\Occasion\SerializableOccasion;
use App\Domain\Occasion\OccasionRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class EditOccasionAction extends Action
{
    protected $occasionRepository;

    public function __construct(LoggerInterface $logger, OccasionRepository $occasionRepository)
    {
        parent::__construct($logger);
        $this->occasionRepository = $occasionRepository;
    }

    protected function action(): Response
    {
        $this->assertAuth();
        $this->assertUtilisateurAuthEstAdmin();
        
        $occasion = $this->occasionRepository->read((int) $this->resolveArg('idOccasion'));
        $body = $this->getFormData();
        if (isset($body['titre'])) $occasion->setTitre($body['titre']);

        $occasion = $this->occasionRepository->update($occasion);
        return $this->respondWithData(new SerializableOccasion($occasion));
    }
}
