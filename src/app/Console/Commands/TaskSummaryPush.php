<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TaskReport;

class TaskSummaryPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crutils:tasks:summary:push {service-name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the task summary to the dashboard platform, expects the service name as argument';

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
        $ts = new TaskReport($this->argument('service-name'));
        return 0;
    }
}
