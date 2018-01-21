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
        $str = "sqlsrv:Server=".$conf['host'];
        $pdo = new \PDO ($str, $conf['username'], $conf['password']);

        $pdo->exec('
            CREATE DATABASE ShopGo_Development;
        ');
    }
}
