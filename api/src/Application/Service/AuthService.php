<?php

declare(strict_types=1);

namespace App\Application\Service;

use Exception;
use Firebase\JWT\JWT;

class AuthService
{
  /**
   * @var string
   */
  private $defaultSettings;

  public function __construct(array $defaultSettings = null)
  {
    $this->defaultSettings = array_merge(
      [
        'algo' => 'RS256',
        'validite' => 3600,
      ],
      $defaultSettings ?: []
    );
    
    if (!($this->defaultSettings['clePrivee'] = file_get_contents($this->defaultSettings['fichierClePrivee']))) {
      throw new \Exception("Impossible de lire la clé privée dans {$this->defaultSettings['fichierClePrivee']}");
    }
    if (!($this->defaultSettings['clePublique'] = file_get_contents($this->defaultSettings['fichierClePublique']))) {
      throw new \Exception("Impossible de lire la clé publique dans {$this->defaultSettings['fichierClePublique']}");
    }
  }

  /**
   * Authentifie l'expéditeur d'une requête par son 'authorization' header
   */
  public function authentifie(string $authorizationHeader): int
  {
    if (strpos($authorizationHeader, "Bearer ") !== 0) {
      throw new AuthPasDeBearerTokenException();
    }

    $token = substr($authorizationHeader, strlen("Bearer "));
    return $this->decodeBearerToken($token);
  }

  /**
   * Decode un bearer token et retourne l'id de l'utilisateur authentifié
   */
  public function decodeBearerToken(string $token, $settings = null): int
  {
    $settings = array_merge($this->defaultSettings, $settings ?: []);
    $payload = JWT::decode($token, $settings['clePublique'], [$settings['algo']]);
    return $payload->sub;
  }

  /**
   * Encode un bearer token contenant l'id de l'utilisateur authentifié
   */
  public function encodeBearerToken(int $idUtilisateurAuth, $settings = null): string
  {
    $settings = array_merge($this->defaultSettings, $settings ?: []);
    $payload = [
      "sub" => $idUtilisateurAuth,
      "exp" => \time() + $settings['validite'],
    ];
    return JWT::encode($payload, $settings['clePrivee'], $settings['algo']);
  }
}
