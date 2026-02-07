<?php

declare(strict_types=1);

namespace App\Dom\Model;

use DateTime;

interface AuthCode
{
    public function getId(): int;
    public function getUtilisateurId(): int;
    public function getExpiresAt(): DateTime;
    public function getUsedAt(): ?DateTime;
    public function getCreatedAt(): DateTime;

    public function estExpire(): bool;
    public function estUtilise(): bool;

    public function verifieCode(string $codeClair): bool;
}
