<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210215211348 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tkdo_participation DROP FOREIGN KEY FK_4DCC53CF14C85134');
        $this->addSql('ALTER TABLE tkdo_participation DROP FOREIGN KEY FK_4DCC53CF81763F5C');
        $this->addSql('DROP INDEX IDX_4DCC53CF81763F5C ON tkdo_participation');
        $this->addSql('DROP INDEX IDX_4DCC53CF14C85134 ON tkdo_participation');
        $this->addSql('ALTER TABLE tkdo_participation DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE tkdo_participation ADD occasionadaptor_id INT NOT NULL, ADD utilisateuradaptor_id INT NOT NULL, DROP doctrineoccasion_id, DROP doctrineutilisateur_id');
        $this->addSql('ALTER TABLE tkdo_participation ADD CONSTRAINT FK_4DCC53CFE6F41478 FOREIGN KEY (occasionadaptor_id) REFERENCES tkdo_occasion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tkdo_participation ADD CONSTRAINT FK_4DCC53CFD2DC28D1 FOREIGN KEY (utilisateuradaptor_id) REFERENCES tkdo_utilisateur (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4DCC53CFE6F41478 ON tkdo_participation (occasionadaptor_id)');
        $this->addSql('CREATE INDEX IDX_4DCC53CFD2DC28D1 ON tkdo_participation (utilisateuradaptor_id)');
        $this->addSql('ALTER TABLE tkdo_participation ADD PRIMARY KEY (occasionadaptor_id, utilisateuradaptor_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tkdo_participation DROP FOREIGN KEY FK_4DCC53CFE6F41478');
        $this->addSql('ALTER TABLE tkdo_participation DROP FOREIGN KEY FK_4DCC53CFD2DC28D1');
        $this->addSql('DROP INDEX IDX_4DCC53CFE6F41478 ON tkdo_participation');
        $this->addSql('DROP INDEX IDX_4DCC53CFD2DC28D1 ON tkdo_participation');
        $this->addSql('ALTER TABLE tkdo_participation DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE tkdo_participation ADD doctrineoccasion_id INT NOT NULL, ADD doctrineutilisateur_id INT NOT NULL, DROP occasionadaptor_id, DROP utilisateuradaptor_id');
        $this->addSql('ALTER TABLE tkdo_participation ADD CONSTRAINT FK_4DCC53CF14C85134 FOREIGN KEY (doctrineutilisateur_id) REFERENCES tkdo_utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tkdo_participation ADD CONSTRAINT FK_4DCC53CF81763F5C FOREIGN KEY (doctrineoccasion_id) REFERENCES tkdo_occasion (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4DCC53CF81763F5C ON tkdo_participation (doctrineoccasion_id)');
        $this->addSql('CREATE INDEX IDX_4DCC53CF14C85134 ON tkdo_participation (doctrineutilisateur_id)');
        $this->addSql('ALTER TABLE tkdo_participation ADD PRIMARY KEY (doctrineoccasion_id, doctrineutilisateur_id)');
    }
}
