<?php

return [
  'table_storage' => [
    'table_name' => 'tkdo_doctrine_migration_versions',
    'version_column_name' => 'version',
    'version_column_length' => 128,
    'executed_at_column_name' => 'executed_at',
    'execution_time_column_name' => 'execution_time',
  ],

  'migrations_paths' => [
    'App\Infrastructure\Persistence\Migrations' => '/src/Infrastructure/Persistence/Migrations',
  ],

  'all_or_nothing' => true,
  'check_database_platform' => true,
];
