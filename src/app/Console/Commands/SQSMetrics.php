<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SQSMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crutils:sqs:metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'returns list of all SQS metrics we monitor';

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
       $sqs = new \App\SQSMetric();
       return 0;
    }
}
