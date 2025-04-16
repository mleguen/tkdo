<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use Psr\Http\Message\ResponseInterface as Response;

class ViewConnexionAction extends ConnexionAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $connexionId = (int) $this->resolveArg('id');
        $connexion = $this->connexionRepository->findConnexionOfId($connexionId);

        $this->logger->info("Connexion of id `{$connexionId}` was viewed.");

        return $this->respondWithData($connexion);
    }
}
