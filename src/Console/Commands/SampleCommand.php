<?php

namespace ImoTikuwa\ModelGenerator\Console\Commands;

use Illuminate\Console\Command;

class SampleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tikuwa:sample';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sample command.';

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
        $this->info('sample command start.');
        $this->info('sample command end.');

        return Command::SUCCESS;
    }
}
