<?php

namespace App\Console\Commands\Database\MsSql;

use Illuminate\Console\Command;

class DeleteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:mssql:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete MSSQL Database';

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
        if($sqlCmd->file(database_path('mssql/database/down.sql'))) {
            $this->info('Deleted mssql database');
        }
        else {
            $this->error('Failed to delete mssql database');
            $this->info(print_r($sqlCmd->getOutput(),true));
        }

    }
}
