<?php

namespace ImoTikuwa\ModelGenerator\Console\Commands;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\{BigIntType, DateTimeType, IntegerType, StringType, TextType};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tikuwa:info {--c|connection=} {--t|table=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays a list of tables and a list of columns contained in the table.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $database_connection_name = $this->hasOption('connection') ? $this->option('connection') : env('DB_CONNECTION', 'mysql');

        /** @var \Illuminate\Database\Connection $connection */
        $connection = DB::connection($database_connection_name);
        $schema = $connection->getDoctrineSchemaManager();

        $tables = $schema->listTableNames();
        $this->table(['table'], array_map(fn($table): array => [$table], $tables));

        $option_tables = $this->option('table');
        if (!empty($option_tables)) {
            $tables = array_intersect($tables, $option_tables);
            if (empty($tables)) {
                $this->error('The table specified in the option was not found.');
                return Command::FAILURE;
            }
        }

        foreach ($tables as $table) {
            $columns = $schema->listTableColumns($table);
            $column_data = array_map(function($column) {
                /** @var \Doctrine\DBAL\Schema\Column $column */
                return [
                    $column->getName(),
                    $this->convertDoctrineColumn($column),
                    $column->getComment()
                ];
            }, $columns);

            $this->info("\n{$table}");
            $this->table(['column name', 'column type', 'comment'], $column_data);
        }

        return Command::SUCCESS;
    }

    /**
     * Replace column types Doctrine\DBAL\Types to Laravel use
     * @param \Doctrine\DBAL\Schema\Column $column
     * @return string|null
     */
    private function convertDoctrineColumn(Column $column = null)
    {
        if (is_null($column)) {
            return null;
        }

        $class_text = get_class($column->getType());
        $column_type = null;
        switch ($class_text) {
            case TextType::class:
            case StringType::class:
                $column_type = 'varchar';
                break;
            case BigIntType::class:
                $column_type = 'bigint';
                break;
            case IntegerType::class:
                $column_type = 'int';
                break;
            case DateTimeType::class:
                $column_type = 'datetime';
                break;
        }

        return $column_type;
    }
}
