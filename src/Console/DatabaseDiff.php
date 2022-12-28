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
    protected $signature = 'database:diff {target-connection}';

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
        $diff = new Diff($this->argument('target-connection'));
        $sqlArr = $diff->preview();
        if(empty($sqlArr)){
            $this->output->info('无差异sql');
        }else{
            $directory = $this->getDirectory();
            if(!is_dir($directory)){
                mkdir($directory,0755,true);
            }
            $identifier = $diff->getManager()->identifier();
            $path = $directory.DIRECTORY_SEPARATOR.$identifier.'-'.date('Y_m_d_His').'.sql';
            $backPath = $path.'.back';
            $this->clearRepeatFile($identifier);
            if($this->putFile($path,$sqlArr) && $this->putFile($backPath,$diff->getManager()->backupSql())){
                $this->output->success("CREATED SUCCESS [{$path}]");
            }
        }
        return Command::SUCCESS;
    }
    protected function putFile($path,$sqlArr){
        return file_put_contents($path,implode(';'.PHP_EOL.PHP_EOL, $sqlArr));
    }

    protected function clearRepeatFile($identifier){
        foreach (glob($this->getDirectory().DIRECTORY_SEPARATOR.'*') as $file){
            if(strpos($file,$identifier) !== false){
                unlink($file);
            }
        }
    }
    protected function getDirectory(){
        return database_path('difference');
    }
}
