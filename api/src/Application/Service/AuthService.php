<?php

declare(strict_types=1);

namespace App\Application\Service;

use Firebase\JWT\JWT;
use Psr\Container\ContainerInterface;

class AuthService
{
  private $defaultSettings;

  public function __construct(ContainerInterface $c)
  {
    $this->defaultSettings = array_merge([
      'algo' => 'RS256',
      'validite' => 3600,
    ], $c->get('settings')['auth']);
    
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
  public function authentifie(string $authorizationHeader): array
  {
    if (
      preg_match('/^Bearer (.+)$/', $authorizationHeader, $matches) ||
      // Le token peut également fourni comme utilisateur (sans mot de passe)
      // d'une authentification basique (par exemple avec `curl -u $token:`)
      (
        preg_match('/^Basic ([^:]+)$/', $authorizationHeader, $matches) &&
        preg_match('/^([^:]+):$/', base64_decode($matches[1]), $matches)
      )
    ) {
      return $this->decodeAuthToken($matches[1]);
    }

    throw new AuthPasDeTokenException();
  }

  /**
   * Decode un bearer token et retourne l'id de l'utilisateur authentifié
   * et s'il est ou non admin
   */
  public function decodeAuthToken(string $token, $settings = []): array
  {
    $settings = array_merge($this->defaultSettings, $settings);
    $payload = JWT::decode($token, $settings['clePublique'], [$settings['algo']]);
    return [
      "idUtilisateurAuth" => $payload->sub,
      "estAdmin" => isset($payload->adm) && $payload->adm,
    ];
  }

  /**
   * Encode un bearer token contenant l'id de l'utilisateur authentifié
   * et s'il est ou non admin
   */
  public function encodeAuthToken(int $idUtilisateurAuth, bool $estAdmin, $settings = []): string
  {
    $settings = array_merge($this->defaultSettings, $settings);
    $payload = [
      "sub" => $idUtilisateurAuth,
      "exp" => \time() + $settings['validite'],
      "adm" => $estAdmin,
    ];
    return JWT::encode($payload, $settings['clePrivee'], $settings['algo']);
  }
}
