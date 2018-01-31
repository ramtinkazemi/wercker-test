<?php

namespace App\Console\Commands\Sync;

use Illuminate\Console\Command;

class ShopGoMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crutils:sync:shopgo:members:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync summary info for shopgo members';

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
        $s = new \App\MembersShopGo\MemberSync([]);
    }
}
