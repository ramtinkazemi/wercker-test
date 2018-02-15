<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestNewMemberRisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crutils:test:new:member:risk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'temp command to test cyfe';

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
        $m = new \App\MemberRisk\CyfeMemberRisk();
    }
}
