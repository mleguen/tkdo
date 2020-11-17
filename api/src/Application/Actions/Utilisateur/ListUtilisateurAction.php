<?php
declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use Psr\Http\Message\ResponseInterface as Response;

class ListUtilisateurAction extends UtilisateurAction
{
    protected function action(): Response
    {
        parent::action();
        $this->assertUtilisateurAuthEstAdmin();
        $utilisateurs = $this->utilisateurRepository->readAll();
        return $this->respondWithData(
            array_map(
                function($utilisateur) {
                    return new SerializableUtilisateur($utilisateur, true);
                },
                $utilisateurs
            )
        );
    }
}
