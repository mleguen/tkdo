<?php
declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Actions\Action;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class UtilisateurAction extends Action
{
    /**
     * @var UtilisateurRepository
     */
    protected $utilisateurRepository;

    /**
     * @var int
     */
    protected $idUtilisateur;

    /**
     * @param LoggerInterface $logger
     * @param UtilisateurRepository  $utilisateurRepository
     */
    public function __construct(LoggerInterface $logger, UtilisateurRepository $utilisateurRepository)
    {
        parent::__construct($logger);
        $this->utilisateurRepository = $utilisateurRepository;
    }

    protected function action(): Response
    {
        $this->assertAuth();
        $this->idUtilisateur = (int) $this->resolveArg('idUtilisateur');
        $this->assertUtilisateurAuthEst($this->idUtilisateur, "L'utilisateur authentifié n'est pas l'utilisateur ($this->idUtilisateur)");

        return $this->response;
    }
}
