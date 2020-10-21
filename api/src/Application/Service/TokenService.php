<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Mock\MockData;
use Exception;
use Firebase\JWT\JWT;

class TokenService
{
  /**
   * Decode un token encodé par le service
   */
  public function decode(string $token): int
  {
    $payload = JWT::decode($token, MockData::getClePublique(), ['RS256']);

    if (!isset($payload->id)) {
      throw new Exception('Payload invalide !');
    }
    return $payload->id;
  }

  /**
   * Encode un token
   */
  public function encode(int $idUtilisateurAuth): string
  {
    $payload = ["id" => $idUtilisateurAuth];
    return JWT::encode($payload, MockData::getClePrive(), 'RS256');
  }
}
