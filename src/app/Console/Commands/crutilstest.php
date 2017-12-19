<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class crutilstest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crutils:debug:db:settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test db settings';

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
        //
        echo env('DB_HOST')."\n";echo env('DB_DATABASE')."\n";

	    echo env('DB_USERNAME')."\n";
        echo env('DB_PASSWORD')."\n";
        echo env('DB_PORT')."\n";
        echo env('DB_CONNECTION')."\n";

        $this->crutilsConnectionTest();
        $this->mssqlTest();
    }

    public function crutilsConnectionTest()
    {
        $rows = DB::connection('crutils')->select('select * from information_schema.tables limit 2');
        color_dump($rows);
    }


    public function mssqlTest()
    {
        $users = DB::connection('sqlsrv')->select('select TOP 2 * from Member');
        color_dump($users);
    }
}
