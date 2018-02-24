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
    protected $signature = 'crutils:geckoboard:update'; //@todo change command name

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the metrics in various dashboards';

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
        // update cohort for customer
        $cc = new \App\CohortCustomer();
        // update pending transactions
        $pt = new \App\ESTransactionsApprovals\TransactionsPending([]);
        //update member risk pending @todo enable this once the micro service call is in place
        // $mrp = new \App\MemberRisk\CyfeMemberRiskStatus([]);
        // push the new member risk which uses microservice data
        $m = new \App\MemberRisk\CyfeMemberRisk();
        // crutils log numbers
        $t = new \App\IndexCrutilsLog\CrutilsLog();
    }
}
