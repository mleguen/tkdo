<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Action;
use App\Domain\Idee\IdeeRepository;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpForbiddenException;

abstract class IdeeAction extends Action
{
  /**
   * @var IdeeRepository
   */
  protected $ideeRepository;

  /**
   * @param LoggerInterface $logger
   * @param IdeeRepository  $ideeRepository
   */
  public function __construct(LoggerInterface $logger, IdeeRepository $ideeRepository)
  {
    parent::__construct($logger);
    $this->ideeRepository = $ideeRepository;
  }

  protected function assertUtilisateurAuthEstAuteur($idAuteur) {
    $this->assertUtilisateurAuthEst($idAuteur, "L'utilisateur authentifié n'est pas l'auteur de l'idée ($idAuteur)");
  }
}
