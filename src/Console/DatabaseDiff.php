<?php

namespace DatabaseDiff\Console;


use DatabaseDiff\Database\Diff;
use Illuminate\Console\Command;

class DatabaseDiff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:diff {source-connection} {--connection=mysql}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database table difference';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $diff = new Diff($this->argument('source-connection'),$this->option('connection'));
        $sqlArr = $diff->preview();
        foreach ($sqlArr as $sql){
            $this->warn($sql.';');
            $this->newLine();
        }
        if(empty($sqlArr)){
            $this->output->info('无差异sql');
        }else if($this->confirm('确认执行当前差异sql？')){
            if($diff->exec()){
                $this->output->success('执行成功');
            }
        }
        return Command::SUCCESS;
    }
}
