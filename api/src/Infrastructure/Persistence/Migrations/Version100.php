<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version100 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tkdo_occasion (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tkdo_participation (doctrineoccasion_id INT NOT NULL, doctrineutilisateur_id INT NOT NULL, INDEX IDX_4DCC53CF81763F5C (doctrineoccasion_id), INDEX IDX_4DCC53CF14C85134 (doctrineutilisateur_id), PRIMARY KEY(doctrineoccasion_id, doctrineutilisateur_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tkdo_utilisateur (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(255) NOT NULL, mdp VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tkdo_idee (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, auteur_id INT NOT NULL, description VARCHAR(255) NOT NULL, dateProposition DATETIME NOT NULL, INDEX IDX_D67D66B9FB88E14F (utilisateur_id), INDEX IDX_D67D66B960BB6FE6 (auteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tkdo_resultat_tirage (occasion_id INT NOT NULL, quiOffre_id INT NOT NULL, quiRecoit_id INT NOT NULL, INDEX IDX_C560DB6A4034998F (occasion_id), INDEX IDX_C560DB6A6417A899 (quiOffre_id), INDEX IDX_C560DB6A891C8F2 (quiRecoit_id), UNIQUE INDEX unique_quiRecoit_id_idx (occasion_id, quiRecoit_id), PRIMARY KEY(occasion_id, quiOffre_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tkdo_participation ADD CONSTRAINT FK_4DCC53CF81763F5C FOREIGN KEY (doctrineoccasion_id) REFERENCES tkdo_occasion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tkdo_participation ADD CONSTRAINT FK_4DCC53CF14C85134 FOREIGN KEY (doctrineutilisateur_id) REFERENCES tkdo_utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tkdo_idee ADD CONSTRAINT FK_D67D66B9FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES tkdo_utilisateur (id)');
        $this->addSql('ALTER TABLE tkdo_idee ADD CONSTRAINT FK_D67D66B960BB6FE6 FOREIGN KEY (auteur_id) REFERENCES tkdo_utilisateur (id)');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage ADD CONSTRAINT FK_C560DB6A4034998F FOREIGN KEY (occasion_id) REFERENCES tkdo_occasion (id)');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage ADD CONSTRAINT FK_C560DB6A6417A899 FOREIGN KEY (quiOffre_id) REFERENCES tkdo_utilisateur (id)');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage ADD CONSTRAINT FK_C560DB6A891C8F2 FOREIGN KEY (quiRecoit_id) REFERENCES tkdo_utilisateur (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tkdo_participation DROP FOREIGN KEY FK_4DCC53CF81763F5C');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage DROP FOREIGN KEY FK_C560DB6A4034998F');
        $this->addSql('ALTER TABLE tkdo_participation DROP FOREIGN KEY FK_4DCC53CF14C85134');
        $this->addSql('ALTER TABLE tkdo_idee DROP FOREIGN KEY FK_D67D66B9FB88E14F');
        $this->addSql('ALTER TABLE tkdo_idee DROP FOREIGN KEY FK_D67D66B960BB6FE6');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage DROP FOREIGN KEY FK_C560DB6A6417A899');
        $this->addSql('ALTER TABLE tkdo_resultat_tirage DROP FOREIGN KEY FK_C560DB6A891C8F2');
        $this->addSql('DROP TABLE tkdo_occasion');
        $this->addSql('DROP TABLE tkdo_participation');
        $this->addSql('DROP TABLE tkdo_utilisateur');
        $this->addSql('DROP TABLE tkdo_idee');
        $this->addSql('DROP TABLE tkdo_resultat_tirage');
    }
}
