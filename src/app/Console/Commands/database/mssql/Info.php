<?php

namespace App\Console\Commands\Database\MsSql;

use Illuminate\Console\Command;

class Info extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:mssql:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get MSSQL Database Configuration infor';

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
            $conf = [
                        'sqlsrv' =>  \DB::connection('sqlsrv')->getConfig(),
                        'sqlsrv_unittest' =>  \DB::connection('sqlsrv_unittest')->getConfig(),
                    ];

            color_dump($conf);
    }
}
