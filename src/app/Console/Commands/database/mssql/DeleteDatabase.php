<?php

namespace App\Console\Commands\Database\MsSql;

use Illuminate\Console\Command;

class DeleteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:mssql:delete
                            {--y : Bypass confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete MSSQL Database';

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
            return ;
        }

        $skip_confirm = $this->option('y');
        if (($skip_confirm === true )
            || $this->confirm('Are you sure really want to ETI all MerchantSearch records'. ' ?')) {

            $conf = \DB::connection('sqlsrv_unittest')->getConfig();
            color_dump($conf);
            $sqlCmd = new \App\Helpers\SqlCmd($conf);
            if ($sqlCmd->file(database_path('mssql/database/down.sql'))) {
                $this->info('Deleted mssql database');
            } else {
                $this->error('Failed to delete mssql database');
                $this->info(print_r($sqlCmd->getOutput(), true));
            }
        }

    }

    public function schemaDown()
    {
        color_dump('mssql down');
        //$res = (new \App\Helpers\SqlCmd())->outputOnError()->file(database_path('/mssql/database/down.sql'));
        /*
        $res = (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[ReportSubscription]');
        $res = (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Campaign]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Transaction]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[TransactionType]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[TransactionStatus]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[GstStatus]');
        (new \App\Helpers\SqlCmd())->query('DROP VIEW [dbo].[MerchantView]');
        (new \App\Helpers\SqlCmd())->query('DROP VIEW [dbo].[MerchantOfferCountView]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Offer]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[ConsolidatedMerchantTierView]');
        (new \App\Helpers\SqlCmd())->query('DROP VIEW [dbo].[MerchantTierView]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[MerchantTierClient]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[MerchantClientMap]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[MerchantTier]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[MerchantTierType]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[MerchantTierCommType]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[MerchantTierAlias]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Currency]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[MerchantAlias]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Merchant]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Network]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[TimeZone]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Country]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[MemberClicks]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Member]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Sp_PublishChanges]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[Client]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[ClientType]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[ClientRewardType]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[ClientProgramType]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[ClientLayoutTheme]');
        (new \App\Helpers\SqlCmd())->query('DROP TABLE [dbo].[ClientAccessType]');
        (new \App\Helpers\SqlCmd())->query('DROP COLUMN ENCRYPTION KEY [PIIColumnEncryptionKey]');
        (new \App\Helpers\SqlCmd())->query('DROP COLUMN MASTER KEY [PIIColumnMasterKey]');
        */
    }
}
