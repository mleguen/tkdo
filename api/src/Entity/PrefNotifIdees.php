<?php

namespace App\Entity;

/**
 * This enum represents the preferences for idea notifications.
 */
enum PrefNotifIdees: string
{
    case JAMAIS = 'jamais';
    case QUOTIDIENNE = 'quotidienne';
    case HEBDOMADAIRE = 'hebdomadaire';
}
