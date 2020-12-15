<?php

declare(strict_types=1);

namespace App\Appli\Service;

use App\Appli\Exception\AuthTokenInvalideException;
use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Settings\AuthSettings;
use Exception;
use Firebase\JWT\JWT;

class AuthService
{
    private $clePrivee;
    private $clePublique;
    private $settings;

    public function __construct(AuthSettings $settings)
    {
        $this->settings = $settings;
        
        if (!($this->clePrivee = file_get_contents($this->settings->fichierClePrivee))) {
            throw new Exception("Impossible de lire la clé privée dans {$this->settings->fichierClePrivee}");
        }
        if (!($this->clePublique = file_get_contents($this->settings->fichierClePublique))) {
            throw new Exception("Impossible de lire la clé publique dans {$this->settings->fichierClePublique}");
        }
    }

    /**
     * Decode un bearer token et retourne une authentification
     * 
     * @throws AuthTokenInvalideException
     */
    public function decode(string $token): AuthAdaptor
    {
        try {
            $payload = JWT::decode($token, $this->clePublique, [$this->settings->algo]);
            return new AuthAdaptor(
                intval($payload->sub),
                isset($payload->adm) && $payload->adm
            );
        }
        catch (Exception $err) {
            throw new AuthTokenInvalideException();
        }
    }

    /**
     * Encode un bearer token contenant l'authentification
     */
    public function encode(AuthAdaptor $auth): string
    {
        $payload = [
            "sub" => $auth->getIdUtilisateur(),
            "exp" => \time() + $this->settings->validite,
            "adm" => $auth->estAdmin(),
        ];
        return JWT::encode($payload, $this->clePrivee, $this->settings->algo);
    }
}
