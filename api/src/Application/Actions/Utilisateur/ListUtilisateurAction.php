<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;

class ListUtilisateurAction extends UtilisateurAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $utilisateurs = $this->utilisateurRepository->findAll();

        $this->logger->info("Utilisateur list was viewed.");

        return $this->respondWithData($utilisateurs);
    }
}
