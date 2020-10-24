<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201024131506 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Renommage de tkdo_resultat_tirage en resultat_tirage';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tkdo_resultat (occasion_id INT NOT NULL, quiOffre_id INT NOT NULL, quiRecoit_id INT NOT NULL, INDEX IDX_C30A2EFD4034998F (occasion_id), INDEX IDX_C30A2EFD6417A899 (quiOffre_id), INDEX IDX_C30A2EFD891C8F2 (quiRecoit_id), UNIQUE INDEX unique_quiRecoit_id_idx (occasion_id, quiRecoit_id), PRIMARY KEY(occasion_id, quiOffre_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tkdo_resultat ADD CONSTRAINT FK_C30A2EFD4034998F FOREIGN KEY (occasion_id) REFERENCES tkdo_occasion (id)');
        $this->addSql('ALTER TABLE tkdo_resultat ADD CONSTRAINT FK_C30A2EFD6417A899 FOREIGN KEY (quiOffre_id) REFERENCES tkdo_utilisateur (id)');
        $this->addSql('ALTER TABLE tkdo_resultat ADD CONSTRAINT FK_C30A2EFD891C8F2 FOREIGN KEY (quiRecoit_id) REFERENCES tkdo_utilisateur (id)');
        $this->addSql('DROP TABLE tkdo_resultat_tirage');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tkdo_resultat_tirage (occasion_id INT NOT NULL, quiOffre_id INT NOT NULL, quiRecoit_id INT NOT NULL, UNIQUE INDEX unique_quiRecoit_id_idx (occasion_id, quiRecoit_id), INDEX IDX_C560DB6A4034998F (occasion_id), INDEX IDX_C560DB6A891C8F2 (quiRecoit_id), INDEX IDX_C560DB6A6417A899 (quiOffre_id), PRIMARY KEY(occasion_id, quiOffre_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage ADD CONSTRAINT FK_C560DB6A4034998F FOREIGN KEY (occasion_id) REFERENCES tkdo_occasion (id)');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage ADD CONSTRAINT FK_C560DB6A6417A899 FOREIGN KEY (quiOffre_id) REFERENCES tkdo_utilisateur (id)');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage ADD CONSTRAINT FK_C560DB6A891C8F2 FOREIGN KEY (quiRecoit_id) REFERENCES tkdo_utilisateur (id)');
        $this->addSql('DROP TABLE tkdo_resultat');
    }
}
