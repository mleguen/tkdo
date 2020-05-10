<?php

return [
  'name' => 'Tkdo Migrations',
  'migrations_namespace' => 'App\Infrastructure\Persistence\Migrations',
  'table_name' => 'tkdo_doctrine_migration_versions',
  'column_name' => 'version',
  'column_length' => 14,
  'executed_at_column_name' => 'executed_at',
  'migrations_directory' => '/src/Infrastructure/Persistence/Migrations',
  'all_or_nothing' => true,
  'check_database_platform' => true,
];
