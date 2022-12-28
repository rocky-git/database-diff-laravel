<?php

namespace DatabaseDiff\Console;

use DatabaseDiff\Database\Manager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseRun extends DatabaseDiff
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:diff-run {--connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database table difference sql run';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        do{
            $manager = new Manager($this->option('connection') ?? DB::getDefaultConnection());
            $file = $this->getSqlFile($manager->identifier());

            if ($file) {
                $sql = implode(PHP_EOL, file($file));
                if ($manager->execSql($sql)) {
                    $this->output->success("RUN DIFF SQL SUCCESS [{$file}]");
                }
            }
        }while($file);
       
        return Command::SUCCESS;
    }

    protected function getSqlFile($identifier)
    {
        foreach (glob($this->getDirectory() . DIRECTORY_SEPARATOR . '*.sql') as $file) {
            if (strpos($file, $identifier) !== false) {
                return $file;
            }
        }
        return null;
    }
}
