<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase as ParentRefreshDatabase;
use DB;

trait RefreshDatabase
{
    use ParentRefreshDatabase {
        ParentRefreshDatabase::refreshDatabase as parentRefreshDatabase;
    }

    public function refreshDatabase()
    {
        $this->parentRefreshDatabase();

        color_dump('Refresh MSSQL Database');

        $conf = DB::connection('sqlsrv')->getConfig();
        color_dump($conf);

        // dsn string With Port Number does not work
        //$str = "sqlsrv:Server=".$conf['host'].":".$conf['port'];
        $str = "sqlsrv:Server=" . $conf['host'];
        $pdo = new \PDO ($str, $conf['username'], $conf['password']);

        $pdo->exec('CREATE DATABASE ShopGo_Development;');

        $pdo->exec( file_get_contents( __DIR__ . '/database/Security/PIIColumnMasterKey.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/Security/PIIColumnEncryptionKey.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/ClientAccessType.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/ClientLayoutTheme.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/ClientProgramType.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/ClientRewardType.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/ClientType.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/Client.sql') );

        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Stored Procedures/Sp_PublishChanges.sql') );

        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/Member.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/MemberClicks.sql') );

        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/Country.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/TimeZone.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/Network.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/Merchant.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/MerchantAlias.sql') );

        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/Currency.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/MerchantTierAlias.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/MerchantTierCommType.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/MerchantTierType.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/MerchantTier.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/MerchantClientMap.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/MerchantTierClient.sql') );

        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Views/MerchantView.sql') );

        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/GstStatus.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/TransactionStatus.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/TransactionType.sql') );
        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/Transaction.sql') );

        $pdo->exec( file_get_contents( __DIR__ . '/database/dbo/Tables/Campaign.sql') );

    }


}
