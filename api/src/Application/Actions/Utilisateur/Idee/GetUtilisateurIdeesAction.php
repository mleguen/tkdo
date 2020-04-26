<?php

declare(strict_types=1);

namespace App\Application\Actions\Utilisateur\Idee;

use App\Application\Actions\Utilisateur\UtilisateurAction;
use App\Application\Mock\MockData;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetUtilisateurIdeesAction extends UtilisateurAction
{
  /**
   * @var MockData
   */
  protected $mock;

  /**
   * @param LoggerInterface $logger
   * @param MockData  $mock
   */
  public function __construct(LoggerInterface $logger, UtilisateurRepository $utilisateurRepository, MockData $mock)
  {
    parent::__construct($logger, $utilisateurRepository);
    $this->mock = $mock;
  }

  protected function action(): Response
  {
    parent::action();
    return $this->respondWithData($this->mock->getListeIdees($this->idUtilisateur));
  }
}
