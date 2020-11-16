<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Idee\IdeeAction;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class CreateIdeeAction extends IdeeAction
{
  /**
   * @var UtilisateurRepository
   */
  private $utilisateurRepository;

  public function __construct(
    LoggerInterface $logger,
    IdeeRepository $ideeRepository,
    UtilisateurRepository $utilisateurRepository
  )
  {
    parent::__construct($logger, $ideeRepository);
    $this->utilisateurRepository = $utilisateurRepository;
  }

  protected function action(): Response
  {
    $this->assertAuth();
    $body = $this->getFormData();
    $this->assertUtilisateurAuthEstAuteur($body['idAuteur']);

    $this->ideeRepository->create(
      $this->utilisateurRepository->read($body['idUtilisateur'], true),
      $body['description'],
      $this->utilisateurRepository->read($body['idAuteur'], true),
      new \DateTime()
    );

    return $this->respondWithData();
  }
}
