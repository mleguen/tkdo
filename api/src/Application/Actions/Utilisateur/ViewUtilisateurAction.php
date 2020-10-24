<?php
declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;

class ViewUtilisateurAction extends UtilisateurAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $utilisateurId = (int) $this->resolveArg('id');
        $utilisateur = $this->utilisateurRepository->findUtilisateurOfId($utilisateurId);

        $this->logger->info("Utilisateur of id `${userId}` was viewed.");

        return $this->respondWithData($utilisateur);
    }
}
