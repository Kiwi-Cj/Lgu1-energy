<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLiveSchemaDump extends Command
{
    protected $signature = 'schema:sync-live {--path=database/schema/mysql-schema.sql : Output file path (relative to project root)}';

    protected $description = 'Generate a schema SQL file from the current live database structure';

    public function handle(): int
    {
        $connection = DB::connection();
        $database = (string) $connection->getDatabaseName();
        $outputPath = (string) $this->option('path');
        $absolutePath = base_path($outputPath);

        $rows = $connection->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        foreach ($rows as $row) {
            $data = (array) $row;
            foreach ($data as $key => $value) {
                if (str_starts_with((string) $key, 'Tables_in_')) {
                    $tables[] = (string) $value;
                    break;
                }
            }
        }

        sort($tables, SORT_STRING);

        $lines = [
            '-- LGU Energy schema dump (generated from live DB connection)',
            '-- Database: ' . $database,
            '-- Generated at: ' . now()->format('Y-m-d H:i:s'),
            '',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        foreach ($tables as $table) {
            $safeTable = str_replace('`', '``', $table);
            $createRow = $connection->selectOne("SHOW CREATE TABLE `{$safeTable}`");
            if (! $createRow) {
                continue;
            }

            $createData = (array) $createRow;
            $createSql = (string) ($createData['Create Table'] ?? '');
            if ($createSql === '') {
                foreach ($createData as $key => $value) {
                    if (str_starts_with((string) $key, 'Create ')) {
                        $createSql = (string) $value;
                        break;
                    }
                }
            }

            if ($createSql === '') {
                continue;
            }

            $lines[] = "DROP TABLE IF EXISTS `{$safeTable}`;";
            $lines[] = $createSql . ';';
            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $lines[] = '';

        $directory = dirname($absolutePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($absolutePath, implode(PHP_EOL, $lines));

        $this->info("Schema written: {$outputPath}");
        $this->line('Tables dumped: ' . count($tables));

        return self::SUCCESS;
    }
}

