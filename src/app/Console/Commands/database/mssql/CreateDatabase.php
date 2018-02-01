<?php

namespace App\Console\Commands\Database\MsSql;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class CreateDatabase extends Command
{
    use ConfirmableTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:mssql:create
                            {--y : Bypass confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create MSSQL Database';

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
        if (! \App::environment('testing')) {
            $this->alert('This command is allowed to run only in Test Environment');
            return;
        }

        $skip_confirm = $this->option('y');
        if (($skip_confirm === true )
            || $this->confirm('Are you sure really want to ETI all MerchantSearch records'. ' ?')) {

            $conf = \DB::connection('sqlsrv_unittest')->getConfig();
            color_dump($conf);
            $sqlCmd = new \App\Helpers\SqlCmd($conf);

            $retry = 5;

            do {
                $isReady = $sqlCmd->isServerReady();
            } while (--$retry > 0 AND !$isReady);

            if (!$isReady) {
                $this->error('Failed to connect mssql server');
                return;
            }

            if ($sqlCmd->file(database_path('mssql/database/up.sql'))) {
                $this->info('Created mssql database');
                if ($this->schemaUp()) {
                    $this->info('schema migration success');
                }
                else {
                    $this->error('Failed to migrate schema');
                }
            } else {
                $this->error('Failed to create mssql database');
                $this->info(print_r($sqlCmd->getOutput(), true));
            }
        }

    }

    public function schemaUp()
    {
        if (! \App::environment('testing')) {
            color_dump('mssql up skipped');
            return;
        }

        color_dump('mssql up');
        $conf = \DB::connection('sqlsrv_unittest')->getConfig();
        color_dump($conf);
        $res = (new \App\Helpers\SqlCmd($conf))->database($conf['database'])->file([
            database_path('mssql/database/Security/PIIColumnMasterKey.sql'),
            database_path('mssql/database/Security/PIIColumnEncryptionKey.sql'),
            database_path('mssql/database/dbo/Tables/ClientAccessType.sql'),
            database_path('mssql/database/dbo/Tables/ClientLayoutTheme.sql'),
            database_path('mssql/database/dbo/Tables/ClientProgramType.sql'),
            database_path('mssql/database/dbo/Tables/ClientRewardType.sql'),
            database_path('mssql/database/dbo/Tables/ClientType.sql'),
            database_path('mssql/database/dbo/Tables/Client.sql'),

            database_path('mssql/database/dbo/Stored\ Procedures/Sp_PublishChanges.sql'),

            database_path('mssql/database/dbo/Tables/Member.sql'),
            database_path('mssql/database/dbo/Tables/MemberClicks.sql'),

            database_path('mssql/database/dbo/Tables/Country.sql'),
            database_path('mssql/database/dbo/Tables/TimeZone.sql'),
            database_path('mssql/database/dbo/Tables/Network.sql'),
            database_path('mssql/database/dbo/Tables/Merchant.sql'),
            database_path('mssql/database/dbo/Tables/MerchantAlias.sql'),

            database_path('mssql/database/dbo/Tables/Currency.sql'),
            database_path('mssql/database/dbo/Tables/MerchantTierAlias.sql'),
            database_path('mssql/database/dbo/Tables/MerchantTierCommType.sql'),
            database_path('mssql/database/dbo/Tables/MerchantTierType.sql'),
            database_path('mssql/database/dbo/Tables/MerchantTier.sql'),
            database_path('mssql/database/dbo/Tables/MerchantClientMap.sql'),
            database_path('mssql/database/dbo/Tables/MerchantTierClient.sql'),
            database_path('mssql/database/dbo/Views/MerchantTierView.sql'),
            database_path('mssql/database/dbo/Views/ConsolidatedMerchantTierView.sql'),
            database_path('mssql/database/dbo/Tables/Offer.sql'),
            database_path('mssql/database/dbo/Views/MerchantOfferCountView.sql'),
            database_path('mssql/database/dbo/Views/MerchantView.sql'),

            database_path('mssql/database/dbo/Tables/GstStatus.sql'),
            database_path('mssql/database/dbo/Tables/TransactionStatus.sql'),
            database_path('mssql/database/dbo/Tables/TransactionType.sql'),
            database_path('mssql/database/dbo/Tables/Transaction.sql'),

            database_path('mssql/database/dbo/Tables/Campaign.sql'),

            database_path('mssql/database/dbo/Tables/ReportSubscription.sql'),
        ]);

        return $res;
    }

}
