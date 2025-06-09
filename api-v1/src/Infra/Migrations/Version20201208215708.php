<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201208215708 extends AbstractMigration
{
    #[\Override]
    public function getDescription() : string
    {
        return 'Ajout de la date de suppression des idées';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tkdo_idee ADD dateSuppression DATETIME DEFAULT NULL');
    }

    #[\Override]
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tkdo_idee DROP dateSuppression');
    }
}
