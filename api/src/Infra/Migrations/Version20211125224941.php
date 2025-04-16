<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211125224941 extends AbstractMigration
{
    #[\Override]
    public function getDescription() : string
    {
        return 'Ajout des exclusions pour le tirage automatique';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tkdo_exclusion (quiOffre_id INT NOT NULL, quiNeDoitPasRecevoir_id INT NOT NULL, INDEX IDX_80DDB4EA6417A899 (quiOffre_id), INDEX IDX_80DDB4EA7433AA56 (quiNeDoitPasRecevoir_id), PRIMARY KEY(quiOffre_id, quiNeDoitPasRecevoir_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tkdo_exclusion ADD CONSTRAINT FK_80DDB4EA6417A899 FOREIGN KEY (quiOffre_id) REFERENCES tkdo_utilisateur (id)');
        $this->addSql('ALTER TABLE tkdo_exclusion ADD CONSTRAINT FK_80DDB4EA7433AA56 FOREIGN KEY (quiNeDoitPasRecevoir_id) REFERENCES tkdo_utilisateur (id)');
    }

    #[\Override]
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tkdo_exclusion');
    }
}
