<?php

declare(strict_types=1);

namespace App\Infra\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add index on tkdo_groupe.archive to optimize membership queries filtering archived groups
 */
final class Version20260215120000 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add index on tkdo_groupe.archive column for membership query optimization';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_GROUPE_ARCHIVE ON tkdo_groupe (archive)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_GROUPE_ARCHIVE ON tkdo_groupe');
    }
}
