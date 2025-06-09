<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609193959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE exclusion (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id_utilisateur_1 UUID NOT NULL, id_utilisateur_2 UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DF1686C885F52BE ON exclusion (id_utilisateur_1)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DF1686C11560304 ON exclusion (id_utilisateur_2)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE idee (id UUID NOT NULL, titre VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, supprimee BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id_utilisateur UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DE60E5C50EAE44 ON idee (id_utilisateur)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE occasion (id UUID NOT NULL, nom VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, date_evenement DATE NOT NULL, date_limite_idee DATE NOT NULL, date_limite_participation DATE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id_utilisateur UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A66DCE550EAE44 ON occasion (id_utilisateur)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE resultat (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id_occasion UUID NOT NULL, id_donneur UUID NOT NULL, id_receveur UUID NOT NULL, id_idee UUID DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7DB5DE2DABD3070 ON resultat (id_occasion)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7DB5DE29A0E7E03 ON resultat (id_donneur)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7DB5DE231BB28B2 ON resultat (id_receveur)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E7DB5DE286C6125E ON resultat (id_idee)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE utilisateur (id UUID NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, genre VARCHAR(255) NOT NULL, pref_notif_idees VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exclusion ADD CONSTRAINT FK_DF1686C885F52BE FOREIGN KEY (id_utilisateur_1) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exclusion ADD CONSTRAINT FK_DF1686C11560304 FOREIGN KEY (id_utilisateur_2) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE idee ADD CONSTRAINT FK_DE60E5C50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE occasion ADD CONSTRAINT FK_8A66DCE550EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2DABD3070 FOREIGN KEY (id_occasion) REFERENCES occasion (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE29A0E7E03 FOREIGN KEY (id_donneur) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE231BB28B2 FOREIGN KEY (id_receveur) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE286C6125E FOREIGN KEY (id_idee) REFERENCES idee (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE exclusion DROP CONSTRAINT FK_DF1686C885F52BE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE exclusion DROP CONSTRAINT FK_DF1686C11560304
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE idee DROP CONSTRAINT FK_DE60E5C50EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE occasion DROP CONSTRAINT FK_8A66DCE550EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat DROP CONSTRAINT FK_E7DB5DE2DABD3070
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat DROP CONSTRAINT FK_E7DB5DE29A0E7E03
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat DROP CONSTRAINT FK_E7DB5DE231BB28B2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resultat DROP CONSTRAINT FK_E7DB5DE286C6125E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE exclusion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE idee
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE occasion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE resultat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE utilisateur
        SQL);
    }
}
