<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class ImportProdData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:prod-data
                            {--host= : The host of the production database}
                            {--port=3306 : The port of the production database}
                            {--database= : The name of the production database}
                            {--username= : The username for the production database}
                            {--password= : The password for the production database}
                            {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop local database tables and import schema/data from remote Production DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database clone process...');

        $host = $this->option('host') ?? $this->ask('Please enter the Production DB Host');
        $port = $this->option('port');
        $database = $this->option('database') ?? $this->ask('Please enter the Production DB Name');
        $username = $this->option('username') ?? $this->ask('Please enter the Production DB Username');
        $password = $this->option('password') ?? $this->secret('Please enter the Production DB Password');

        // Configure the remote connection dynamically
        Config::set('database.connections.production_source', [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        try {
            $this->info('Attempting to connect to the production database...');
            DB::connection('production_source')->getPdo();
            $this->info('Successfully connected to Production Database!');
        } catch (\Exception $e) {
            $this->error('Could not connect to Production Database: ' . $e->getMessage());
            $this->warn('Please ensure the remote database allows external connections and the credentials are correct.');
            return 1;
        }

        // Fetch all tables
        $this->info('Fetching production table list...');
        try {
            // Use SHOW FULL TABLES to distinguish between BASE TABLE and VIEW
            $tables = DB::connection('production_source')->select('SHOW FULL TABLES');
        } catch (\Exception $e) {
            $this->error('Failed to fetch table listing: ' . $e->getMessage());
            return 1;
        }

        // Tables to exclude from DATA import (but we still sync structure)
        $excludeDataTables = [
            'failed_jobs',
            'jobs',
            'sessions',
            'cache',
            'cache_locks',
            'job_batches',
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
        ];

        $count = count($tables);

        if (!$this->option('force') && !$this->confirm("This will DROP ALL LOCAL TABLES and import structure/data for {$count} tables from production. This is destructive! Are you sure?", true)) {
            $this->info('Operation cancelled.');
            return 1;
        }

        $this->info('Starting full import...');

        // Disable foreign keys to allow dropping tables and creating them out of order
        Schema::disableForeignKeyConstraints();

        // 1. Drop all local tables
        $this->dropAllLocalTables();

        // 2. Import tables and views
        foreach ($tables as $tableInfo) {
            $tableArray = (array) $tableInfo;
            // The key is usually "Tables_in_dbname" but depends on connection.
            // It's safer to just take the first value as name, second as type.
            $tableName = array_values($tableArray)[0];
            $tableType = array_values($tableArray)[1];

            if ($tableType === 'VIEW') {
                $this->importView($tableName);
            } else {
                $this->importTable($tableName, in_array($tableName, $excludeDataTables));
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->info('Database Clone Completed Successfully!');
        return 0;
    }

    private function dropAllLocalTables()
    {
        $this->info('Dropping all local tables and views...');

        $tables = DB::select('SHOW FULL TABLES');

        foreach ($tables as $table) {
            $tableArray = (array) $table;
            $tableName = array_values($tableArray)[0];
            $tableType = array_values($tableArray)[1];

            if ($tableType === 'VIEW') {
                DB::statement("DROP VIEW IF EXISTS `{$tableName}`");
                $this->line("Dropped View: $tableName");
            } else {
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                $this->line("Dropped Table: $tableName");
            }
        }

        $this->info('Local database cleared.');
    }

    private function importView($viewName)
    {
        $this->info("Importing View: $viewName...");
        try {
            $createResult = DB::connection('production_source')->select("SHOW CREATE VIEW `{$viewName}`");
            if (empty($createResult)) {
                $this->warn("Could not get definition for view: $viewName");
                return;
            }

            // The property name in the object is usually "Create View"
            $createSql = $createResult[0]->{'Create View'};

            // Some MySQL versions include the DEFINER user, which might not exist locally.
            // We should strip it: CREATE ALGORITHM=UNDEFINED DEFINER=`user`@`host` SQL SECURITY DEFINER VIEW...
            // Simple regex to remove DEFINER clause
            $createSql = preg_replace('/DEFINER=`[^`]+`@`[^`]+`/', '', $createSql);

            DB::statement($createSql);
        } catch (\Exception $e) {
            $this->error("Error importing View $viewName: " . $e->getMessage());
        }
    }

    private function importTable($tableName, $excludeData = false)
    {
        $this->info("Importing Table: $tableName...");

        try {
            // 1. Create Table
            $createResult = DB::connection('production_source')->select("SHOW CREATE TABLE `{$tableName}`");
            if (empty($createResult)) {
                $this->warn("Could not get definition for table: $tableName");
                return;
            }
            $createSql = $createResult[0]->{'Create Table'};

            // Strip AUTO_INCREMENT value to start clean if needed, but usually we want to keep it if we import data.
            // Actually, keep it. But ensure IF NOT EXISTS is not needed because we dropped everything.

            DB::statement($createSql);

            // 2. Import Data
            if ($excludeData) {
                $this->line("  - Skipping data import (excluded).");
                return;
            }

            $query = DB::connection('production_source')->table($tableName);
            $totalRecords = $query->count();

            if ($totalRecords === 0) {
                $this->line("  - Table is empty.");
                return;
            }

            $bar = $this->output->createProgressBar($totalRecords);
            $bar->start();

            $chunkSize = 1000;
            $chunk = [];

            // Use cursor to minimize memory usage
            // Note: If table has millions of rows, this might still be slow.
            // For massive tables, mysqldump is preferred, but this is pure PHP solution.
            foreach ($query->cursor() as $record) {
                $chunk[] = (array) $record;

                if (count($chunk) >= $chunkSize) {
                    DB::table($tableName)->insert($chunk);
                    $bar->advance(count($chunk));
                    $chunk = [];
                }
            }

            if (!empty($chunk)) {
                DB::table($tableName)->insert($chunk);
                $bar->advance(count($chunk));
            }

            $bar->finish();
            $this->newLine();
        } catch (\Exception $e) {
            $this->error("Error importing Table $tableName: " . $e->getMessage());
        }
    }
}
