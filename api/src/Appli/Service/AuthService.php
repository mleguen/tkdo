<?php

declare(strict_types=1);

namespace App\Appli\Service;

use App\Appli\Exception\AuthTokenInvalideException;
use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Settings\AuthSettings;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private string $clePrivee;
    private string $clePublique;

    public function __construct(private readonly AuthSettings $settings)
    {
        $clePrivee = file_get_contents($this->settings->fichierClePrivee);
        if ($clePrivee === false) {
            throw new Exception("Impossible de lire la clé privée dans {$this->settings->fichierClePrivee}");
        }
        $this->clePrivee = $clePrivee;

        $clePublique = file_get_contents($this->settings->fichierClePublique);
        if ($clePublique === false) {
            throw new Exception("Impossible de lire la clé publique dans {$this->settings->fichierClePublique}");
        }
        $this->clePublique = $clePublique;
    }

    /**
     * Decode un bearer token et retourne une authentification
     *
     * @throws AuthTokenInvalideException
     */
    public function decode(string $token): AuthAdaptor
    {
        try {
            $key = new Key($this->clePublique, $this->settings->algo);
            $payload = JWT::decode($token, $key);
            /** @var int[] $groupeIds */
            $groupeIds = isset($payload->groupe_ids) ? (array) $payload->groupe_ids : [];
            /** @var int[] $groupeAdminIds */
            $groupeAdminIds = isset($payload->groupe_admin_ids) ? (array) $payload->groupe_admin_ids : [];
            return new AuthAdaptor(
                intval($payload->sub),
                isset($payload->adm) && $payload->adm,
                $groupeIds,
                $groupeAdminIds
            );
        }
        catch (Exception) {
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
            "groupe_ids" => $auth->getGroupeIds(),
            "groupe_admin_ids" => $auth->getGroupeAdminIds(),
        ];
        return JWT::encode($payload, $this->clePrivee, $this->settings->algo);
    }

    /**
     * Get JWT validity in seconds
     */
    public function getValidite(): int
    {
        return $this->settings->validite;
    }
}
