<?php

namespace App\Helpers;

class SqlCmd
{
    protected $server;
    protected $username;
    protected $password;

    protected $database;
    protected $query;
    protected $file;

    protected $output;
    protected $outputOnError;


    public function __construct()
    {
        $this->server = 'mssql';
        $this->username = 'SA';
        $this->password = '<YourStrong!Passw0rd>';
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function outputOnError($on = true)
    {
        $this->outputOnError = $on;
        return $this;
    }

    public function database($database)
    {
        $this->database = $database;
        return $this;
    }

    public function query($query)
    {
        $this->query = $query;
        return $this->exec();
    }

    public function file($files)
    {
        $ret = false;

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files AS $file) {
            $this->file = $file;
            if (! $ret = $this->exec()) {
                break;
            }
        }

        return $ret;
    }

    public function exec()
    {
        $args = [];

        if (isset($this->query)) {
            $args[] = "-Q '{$this->query}'";
        }
        elseif (isset($this->file)) {
            $args[] = "-i {$this->file}";
        }

        if (isset($this->database)) {
            $args[] = "-d {$this->database}";
        }
        $args_str = implode(' ', $args);
        $cmd = "/opt/mssql-tools/bin/sqlcmd -S {$this->server} -U {$this->username} -P '{$this->password}' -b {$args_str}";


        color_dump(['cmd' => str_replace($this->password, 'xxxxxxxx',$cmd)]);

        $lastMsg = exec($cmd, $output, $res);
        $this->output = $output;

        if ($this->outputOnError && $res !== 0) {
            color_dump(['error' => $output]);
        }

         return ($res === 0)? true : false;
    }

    public function refreshDatabase()
    {
        exec(__DIR__ . '/mssql/mssql-migration.sh', $output, $res);
        dd($output, $res);

    }

    public function setupDatabase()
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

        $pdo->exec( file_get_contents(__DIR__ . '/database/Security/PIIColumnMasterKey.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/Security/PIIColumnEncryptionKey.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/ClientAccessType.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/ClientLayoutTheme.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/ClientProgramType.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/ClientRewardType.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/ClientType.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/Client.sql') );

        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Stored Procedures/Sp_PublishChanges.sql') );

        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/Member.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/MemberClicks.sql') );

        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/Country.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/TimeZone.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/Network.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/Merchant.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/MerchantAlias.sql') );

        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/Currency.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/MerchantTierAlias.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/MerchantTierCommType.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/MerchantTierType.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/MerchantTier.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/MerchantClientMap.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/MerchantTierClient.sql') );

        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Views/MerchantView.sql') );

        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/GstStatus.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/TransactionStatus.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/TransactionType.sql') );
        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/Transaction.sql') );

        $pdo->exec( file_get_contents(__DIR__ . '/database/dbo/Tables/Campaign.sql') );

    }


}
