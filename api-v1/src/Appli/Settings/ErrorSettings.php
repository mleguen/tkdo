<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Bootstrap;

class ErrorSettings
{
    /** Affiche le détail des erreurs dans la réponse */
    public $displayErrorDetails;
    /** Affiche les erreurs sur la sortie erreur */
    public $logErrors = true;
    /** Affiche le détail des erreurs sur la sortie erreur */
    public $logErrorDetails = true;

    public function __construct(
        Bootstrap $bootstrap
    )
    {
        $this->displayErrorDetails = $bootstrap->devMode;
    }
}
