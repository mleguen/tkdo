<?php

declare(strict_types=1);

namespace App\Application\Actions\Connexion;

use Psr\Http\Message\ResponseInterface as Response;

class ListConnexionAction extends ConnexionAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $connexions = $this->connexionRepository->findAll();

        $this->logger->info("Connexion list was viewed.");

        return $this->respondWithData($connexions);
    }
}
