<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add failed login attempt tracking columns to tkdo_utilisateur
 */
final class Version20260215140000 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add tentatives_echouees and verrouille_jusqua columns to tkdo_utilisateur';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tkdo_utilisateur ADD tentatives_echouees INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE tkdo_utilisateur ADD verrouille_jusqua DATETIME DEFAULT NULL');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tkdo_utilisateur DROP COLUMN tentatives_echouees');
        $this->addSql('ALTER TABLE tkdo_utilisateur DROP COLUMN verrouille_jusqua');
    }
}
