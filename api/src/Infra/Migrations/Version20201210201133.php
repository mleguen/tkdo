<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201210201133 extends AbstractMigration
{
    public function getDescription() : string
    {
        return "Ajout de la date d'envoi de la dernière notification périodique";
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tkdo_utilisateur ADD dateDerniereNotifPeriodique DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tkdo_utilisateur DROP dateDerniereNotifPeriodique');
    }
}
