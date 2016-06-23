<?php

namespace LucasRuroken\LaraMigrationsGenerator\Commands;

use Illuminate\Console\Command;
use LucasRuroken\LaraMigrationsGenerator\Migrate;


class LaraMigrationsGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:migrations:from-mysql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate migrations from a previous mysql database';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            
            $migrate = new Migrate();
            $migrate->ignore([]);
            $migrate->convert(env('DB_DATABASE'));
            $migrate->write();

            $this->info('Database import successful');
        }
        catch(\Exception $e){
            
            $this->info('An error has occurred, please try again');
        }
        
    }
}
