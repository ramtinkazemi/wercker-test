<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CohortCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crutils:cohort:active';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'build customer cohort';

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
        $cc = new \App\CohortCustomer();
        echo $cc->csv;
    }
}
