<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Bootstrap;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;

class MigrationSettings
{
    public ConfigurationArray $configuration;

    public function __construct(
        Bootstrap $bootstrap
    )
    {
        $this->configuration = new ConfigurationArray([
            'table_storage' => [
                'table_name' => 'tkdo_doctrine_migration_versions',
                'version_column_name' => 'version',
                'version_column_length' => 128,
                'executed_at_column_name' => 'executed_at',
                'execution_time_column_name' => 'execution_time',
            ],

            'migrations_paths' => [
                'App\Infra\Migrations' => "$bootstrap->apiRoot/src/Infra/Migrations",
            ],

            'all_or_nothing' => true,
            'check_database_platform' => true,
        ]);
    }
}
