<?php
declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use Psr\Http\Message\ResponseInterface as Response;

class OneUtilisateurAction extends UtilisateurAction
{
    /**
     * @var int
     */
    protected $idUtilisateur;

    protected function action(): Response
    {
        parent::action();
        $this->idUtilisateur = (int) $this->resolveArg('idUtilisateur');
        $this->assertUtilisateurAuthEst(
            [$this->idUtilisateur],
            "L'utilisateur authentifié n'est pas l'utilisateur ($this->idUtilisateur) et n'est pas admin"
        );

        return $this->response;
    }
}
