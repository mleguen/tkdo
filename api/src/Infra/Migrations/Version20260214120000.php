<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create tkdo_groupe and tkdo_groupe_utilisateur tables for group membership
 */
final class Version20260214120000 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Create tkdo_groupe and tkdo_groupe_utilisateur tables for group membership';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tkdo_groupe (
            id INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(255) NOT NULL,
            archive TINYINT(1) NOT NULL DEFAULT 0,
            date_creation DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE tkdo_groupe_utilisateur (
            groupe_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            est_admin TINYINT(1) NOT NULL DEFAULT 0,
            date_ajout DATETIME NOT NULL,
            INDEX IDX_GROUPE (groupe_id),
            INDEX IDX_UTILISATEUR (utilisateur_id),
            PRIMARY KEY(groupe_id, utilisateur_id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE tkdo_groupe_utilisateur
            ADD CONSTRAINT FK_GROUPE_UTILISATEUR_GROUPE
            FOREIGN KEY (groupe_id) REFERENCES tkdo_groupe (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE tkdo_groupe_utilisateur
            ADD CONSTRAINT FK_GROUPE_UTILISATEUR_UTILISATEUR
            FOREIGN KEY (utilisateur_id) REFERENCES tkdo_utilisateur (id) ON DELETE CASCADE');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tkdo_groupe_utilisateur');
        $this->addSql('DROP TABLE tkdo_groupe');
    }
}
