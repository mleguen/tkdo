<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add index on tkdo_groupe.nom to optimize ORDER BY queries on group name
 */
final class Version20260215130000 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add index on tkdo_groupe.nom column for ORDER BY optimization';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_GROUPE_NOM ON tkdo_groupe (nom)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_GROUPE_NOM ON tkdo_groupe');
    }
}
