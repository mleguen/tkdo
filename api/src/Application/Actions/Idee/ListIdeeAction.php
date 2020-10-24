<?php

declare(strict_types=1);

namespace App\Application\Actions\Idee;

use App\Application\Actions\Idee\IdeeAction;
use App\Application\Serializable\Idee\SerializableIdee;
use App\Application\Serializable\Utilisateur\SerializableUtilisateur;
use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class ListIdeeAction extends IdeeAction
{
  /**
   * @var UtilisateurRepository
   */
  private $utilisateurRepository;

  public function __construct(
    LoggerInterface $logger,
    IdeeRepository $ideeRepository,
    UtilisateurRepository $utilisateurRepository
  ) {
    parent::__construct($logger, $ideeRepository);
    $this->utilisateurRepository = $utilisateurRepository;
  }

  protected function action(): Response
  {
    $this->assertAuth();
    $queryParams = $this->request->getQueryParams();
    if (!isset($queryParams['idUtilisateur'])) throw new HttpBadRequestException($this->request, 'idUtilisateur manquant');

    $idUtilisateurAuth = $this->request->getAttribute('idUtilisateurAuth');

    $utilisateur = $this->utilisateurRepository->read((int) $queryParams['idUtilisateur']);
    return $this->respondWithData([
      "utilisateur" => new SerializableUtilisateur($utilisateur),
      "idees" => array_map(
        function (Idee $i) {
          return new SerializableIdee($i);
        },
        array_values( // Obligatoire pour être encodé comme un tableau en JSON
          array_filter(
            $this->ideeRepository->readByUtilisateur($utilisateur),
            function (Idee $i) use ($idUtilisateurAuth) {
              return (
                // L'utilisateur authentifié ne peut voir que les idées dont il est l'auteur
                ($idUtilisateurAuth === $i->getAuteur()->getId()) ||
                // ou qui ont été proposées pour quelqu'un d'autre que lui
                ($idUtilisateurAuth !== $i->getUtilisateur()->getId())
              );
            }
          )
        )
      ),
    ]);
  }
}
