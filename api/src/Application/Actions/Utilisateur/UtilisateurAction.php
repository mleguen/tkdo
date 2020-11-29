<?php
declare(strict_types=1);

namespace App\Application\Actions\Utilisateur;

use App\Application\Actions\Action;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class UtilisateurAction extends Action
{
    /**
     * @var UtilisateurRepository
     */
    protected $utilisateurRepository;

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

        return $this->response;
    }

    /**
     * Vérifie que l'utilisateur authentifié est admin
     */
    protected function assertEstEmail($email, $warning = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpBadRequestException($this->request, "$email n'est pas un email valide");
            if (!is_null($warning)) {
                $this->logger->warning($warning);
            }
        }
    }
}
