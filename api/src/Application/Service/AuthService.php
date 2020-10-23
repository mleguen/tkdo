<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Mock\MockData;
use Exception;
use Firebase\JWT\JWT;

class AuthService
{
  /**
   * Durée de vie du token en secondes
   * 
   * @var int
   */
  private $dureeDeVie;

  public function __construct(int $dureeDeVie)
  {
    $this->dureeDeVie = $dureeDeVie;
  }

  /**
   * Decode un token encodé par le service
   */
  public function decode(string $token): int
  {
    $payload = JWT::decode($token, MockData::getClePublique(), ['RS256']);

    if (!isset($payload->sub)) {
      throw new Exception('Payload invalide !');
    }
    return $payload->sub;
  }

  /**
   * Encode un token
   */
  public function encode(int $idUtilisateurAuth): string
  {
    $payload = [
      "sub" => $idUtilisateurAuth,
      "exp" => \time() + $this->dureeDeVie,
    ];
    return JWT::encode($payload, MockData::getClePrive(), 'RS256');
  }
}
