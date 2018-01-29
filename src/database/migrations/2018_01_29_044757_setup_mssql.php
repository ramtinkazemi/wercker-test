<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetupMssql extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        color_dump('mssql up');
        //$res = (new \App\Helpers\SqlCmd())->query('select getdate()');
        //$res = (new \App\Helpers\SqlCmd())->outputOnError()->file(database_path('/mssql/database/up.sql'));
        //return $res;

        $conf = DB::connection('sqlsrv')->getConfig();
        color_dump($conf);
        $res = (new \App\Helpers\SqlCmd())->outputOnError()->database($conf['database'])->file([
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        color_dump('mssql down');
        //$res = (new \App\Helpers\SqlCmd())->outputOnError()->file(database_path('/mssql/database/down.sql'));

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
    }
}
