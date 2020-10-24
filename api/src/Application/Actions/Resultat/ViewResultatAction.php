<?php
declare(strict_types=1);

namespace App\Application\Actions\Resultat;

use Psr\Http\Message\ResponseInterface as Response;

class ViewResultatAction extends ResultatAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $resultatId = (int) $this->resolveArg('id');
        $resultat = $this->resultatRepository->findResultatOfId($resultatId);

        $this->logger->info("Resultat of id `${userId}` was viewed.");

        return $this->respondWithData($resultat);
    }
}
