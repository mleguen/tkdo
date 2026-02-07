<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create auth_code table for JWT token exchange flow
 */
final class Version20260131120000 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Create tkdo_auth_code table for secure JWT token exchange';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tkdo_auth_code (
            id INT AUTO_INCREMENT NOT NULL,
            code_hash VARCHAR(255) NOT NULL,
            utilisateur_id INT NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_expires_at (expires_at),
            INDEX idx_utilisateur_id (utilisateur_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE tkdo_auth_code ADD CONSTRAINT FK_AUTH_CODE_UTILISATEUR FOREIGN KEY (utilisateur_id) REFERENCES tkdo_utilisateur (id)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tkdo_auth_code');
    }
}
