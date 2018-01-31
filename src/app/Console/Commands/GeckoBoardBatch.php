<?php

namespace App\Console\Commands; 

use Illuminate\Console\Command;
use App\CRGeckoBoard;

class GeckoBoardBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crutils:geckoboard:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the geckoboard numbers';

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
        $gb = new CRGeckoBoard();
        $gb->sendBatchSQL();

        $cc = new \App\CohortCustomer();
    }
}
