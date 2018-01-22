<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Artisan;

class TaskReportTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTaskProcess()
    {
        $resultAsText  = Artisan::call('crutils:tasks:summary:push', [
            'service-name' => 'crutils'
        ]);
        $this->assertEquals(0, $resultAsText);
    }
/*
    public function testSQScommand()
    {
        $resultAsText  = Artisan::call('crutils:sqs:metrics', [

        ]);
        $this->assertEquals(0, $resultAsText);
    }
*/
}
