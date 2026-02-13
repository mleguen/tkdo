<?php

declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Model\AuthCode;

interface AuthCodeRepository
{
    /**
     * Create a new auth code for the given user.
     * Returns the clear-text code (not stored, only returned once).
     *
     * @return array{code: string, authCode: AuthCode}
     */
    public function create(int $utilisateurId, int $expirySeconds = 60): array;

    /**
     * Find an auth code by ID.
     */
    public function read(int $id): ?AuthCode;

    /**
     * Find all valid (not expired, not used) auth codes for a user.
     *
     * @return AuthCode[]
     */
    public function readValidByUtilisateur(int $utilisateurId): array;

    /**
     * Atomically mark an auth code as used.
     * Returns true if the code was successfully marked (it wasn't already used).
     * Returns false if the code was already used (race condition protection).
     */
    public function marqueUtilise(int $codeId): bool;

    /**
     * Delete auth codes that expired before the given threshold.
     * Removes all expired codes regardless of whether they were used,
     * since expired codes are no longer valid for exchange.
     *
     * TODO: Tech debt — call this via a cron job or scheduled task to prevent
     * unbounded growth of the auth_code table. Codes expire after 60s but rows
     * remain until purged. See Story 1.1 Dev Notes "Cleanup Strategy".
     *
     * @return int Number of rows deleted
     */
    public function purgeExpired(\DateTimeInterface $olderThan): int;
}
