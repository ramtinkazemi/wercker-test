<?php

namespace App\Console\Commands\Database\MsSql;

use Illuminate\Console\Command;

class CreateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:mssql:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create MSSQL Database';

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
     * @return mixed
     */
    public function handle()
    {
        $sqlCmd = new \App\Helpers\SqlCmd();

        $retry = 5;

        do {
            $isReady = $sqlCmd->isServerReady();
        }while ( --$retry > 0 AND ! $isReady );

        if (!$isReady) {
            $this->error('Failed to connect mssql server');
            return;
        }

        if($sqlCmd->file(database_path('mssql/database/up.sql'))) {
            $this->info('Created mssql database');
        }
        else {
            $this->error('Failed to create mssql database');
            $this->info(print_r($sqlCmd->getOutput(),true));
        }

    }
}
