<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

class PrefNotifIdees
{
    const Aucune = 'N';
    const Instantanee = 'I';
    const Quotidienne = 'Q';

    const Periodiques = [
        self::Quotidienne,
    ];
    const Toutes = [
        self::Aucune,
        self::Instantanee,
        self::Quotidienne,
    ];
}
