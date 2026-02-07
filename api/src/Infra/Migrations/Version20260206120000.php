<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add composite index for auth code lookup and ON DELETE CASCADE for user FK.
 *
 * - idx_valid_codes: optimizes the findValidAuthCode query which filters on
 *   used_at IS NULL AND expires_at > NOW().
 * - ON DELETE CASCADE: ensures auth codes are cleaned up when a user is deleted.
 */
final class Version20260206120000 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add composite index on tkdo_auth_code(used_at, expires_at) and ON DELETE CASCADE on user FK';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        // Composite index for the findValidAuthCode query: WHERE used_at IS NULL AND expires_at > :now
        $this->addSql('CREATE INDEX idx_valid_codes ON tkdo_auth_code (used_at, expires_at)');

        // Add ON DELETE CASCADE so auth codes are removed when a user is deleted
        $this->addSql('ALTER TABLE tkdo_auth_code DROP FOREIGN KEY FK_AUTH_CODE_UTILISATEUR');
        $this->addSql('ALTER TABLE tkdo_auth_code ADD CONSTRAINT FK_AUTH_CODE_UTILISATEUR FOREIGN KEY (utilisateur_id) REFERENCES tkdo_utilisateur (id) ON DELETE CASCADE');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_valid_codes ON tkdo_auth_code');

        $this->addSql('ALTER TABLE tkdo_auth_code DROP FOREIGN KEY FK_AUTH_CODE_UTILISATEUR');
        $this->addSql('ALTER TABLE tkdo_auth_code ADD CONSTRAINT FK_AUTH_CODE_UTILISATEUR FOREIGN KEY (utilisateur_id) REFERENCES tkdo_utilisateur (id)');
    }
}
