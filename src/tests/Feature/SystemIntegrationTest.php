<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\RefreshDatabase;
use DB;
//use Tests\RefreshSqlServer;

class SystemIntegrationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSqlsrv()
    {
        $result = DB::connection('sqlsrv')->select('select getdate()');
        $this->assertNotEmpty($result);

        //$this->assertTrue(false);
    }
}
