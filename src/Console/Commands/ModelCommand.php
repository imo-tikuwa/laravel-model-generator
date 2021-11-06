<?php

namespace ImoTikuwa\ModelGenerator\Console\Commands;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\{BigIntType, DateTimeType, IntegerType, StringType, TextType};
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class ModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tikuwa:model {--c|connection=} {--t|table=*} {--f|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model class from an existing table.';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Tables that are not subject to automatic generation.
     *
     * @var array
     */
    protected $skip_tables = [
        'migrations',
    ];

    /**
     * Columns to skip with "fillable".
     *
     * @var array
     */
    protected $fillable_skip_columns = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
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
        $tables = array_diff($tables, $this->skip_tables);

        $option_tables = $this->option('table');
        if (!empty($option_tables)) {
            $tables = array_intersect($tables, $option_tables);
            if (empty($tables)) {
                $this->error('The table specified in the option was not found.');
                return Command::FAILURE;
            }
        }

        $force = $this->option('force');
        $model_path = $this->laravel['path'] . '/Models/{model_name}.php';
        $namespace = $this->laravel->getNamespace() . 'Models';
        foreach ($tables as $table) {

            $model_name = Str::studly(Str::singular($table));
            if (
                !$force &&
                class_exists("{$namespace}\\{$model_name}") &&
                !$this->confirm("{$model_name} already exists. Do you want to overwrite?")
                ) {
                    continue;
            }

            $columns = $schema->listTableColumns($table);
            $columns = array_map(function($column) {
                /** @var \Doctrine\DBAL\Schema\Column $column */
                return [
                    'name' => $column->getName(),
                    'type' => $this->convertDoctrineColumnToLaravelUse($column),
                    'comment' => $column->getComment()
                ];
            }, $columns);

            $path = str_replace('{model_name}', $model_name, $model_path);
            $data = [
                'namespace' => $namespace,
                'class' => $model_name,
                'columns' => $columns,
            ];
            $contents = view('tikuwa::model', $data)->render();
            if (file_put_contents($path, "<?php\n{$contents}")) {
                $this->info("{$model_name} created successfully.");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Replace column types Doctrine\DBAL\Types to Laravel use
     * @param \Doctrine\DBAL\Schema\Column $column
     * @return string|null
     */
    private function convertDoctrineColumnToLaravelUse(Column $column = null)
    {
        if (is_null($column)) {
            return null;
        }

        $class_text = get_class($column->getType());
        $column_phpdoc_type = null;
        switch ($class_text) {
            case TextType::class:
            case StringType::class:
                $column_phpdoc_type = 'string';
                break;
            case BigIntType::class:
            case IntegerType::class:
                $column_phpdoc_type = 'int';
                break;
            case DateTimeType::class:
                $column_phpdoc_type = '\Illuminate\Support\Carbon';
                break;
        }
        if (!$column->getNotnull()) {
            $column_phpdoc_type .= '|null';
        }

        return $column_phpdoc_type;
    }
}
