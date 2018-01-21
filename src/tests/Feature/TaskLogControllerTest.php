<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Artisan;

class TasklogControllerTest extends TestCase
{
    use RefreshDatabase;
/*
    public function setUp()
    {
        if(env('APP_ENV') == "testing") {
            parent::setUp();
            Artisan::call('migrate', [
                '--env' => 'testing'
            ]);
            // Artisan::call('db:seed');
        }
    }
*/

    /**
     * /tasklog home should not return anything
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get('/tasklog');
        $response->assertStatus(400);
    }

    /**
     * TESTS WITHOUT RECORDS PROCESSED
     */

    /**
     * post to /tasklog (create a task)
     */
    public function testSimpleTask(){
        //create task
        $params = ["ServiceName" => 'crutils', "TaskName"=>'crutils'];
        //$response = $this->post('/tasklog', $params, []);
        $response = $this->json('POST', '/tasklog', $params);
        $response->assertStatus(200);
        // test success update
        $updateTaskResponseBody = json_decode($response->getContent(),true);
        $params['TaskLogId'] = $updateTaskResponseBody['TaskLogId'];
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(200);
        // test failed update
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(400);
    }

    public function testTaskNotFound(){
        $params = ["ServiceName" => __CLASS__, "TaskName"=>__FUNCTION__, "TaskLogId"=>0];
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(404);
    }

    public function testTaskRecordsTotalsProcessed(){
        //create task
        $params = ["ServiceName" => __CLASS__, "TaskName"=>__FUNCTION__];
        //$response = $this->post('/tasklog', $params, []);
        $response = $this->json('POST', '/tasklog', $params);
        $response->assertStatus(200);
        // test success update
        $updateTaskResponseBody = json_decode($response->getContent(),true);
        $params['TaskLogId'] = $updateTaskResponseBody['TaskLogId'];
        $params['RecordsProcessed'] = 10;
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(200);
        // test failed update
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(400);
    }

    public function testTaskRecordsTotalsSucessFaillProcessed(){
        //create task
        $params = ["ServiceName" => __CLASS__, "TaskName"=>__FUNCTION__];
        //$response = $this->post('/tasklog', $params, []);
        $response = $this->json('POST', '/tasklog', $params);
        $response->assertStatus(200);
        // test success update
        $updateTaskResponseBody = json_decode($response->getContent(),true);
        $params['TaskLogId'] = $updateTaskResponseBody['TaskLogId'];
        $params['RecordsProcessed'] = 10;
        $params['RecordsProcessedOK'] = 1;
        $params['RecordsProcessedFail'] = 9;
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(200);
        // test failed update
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(400);
    }

    public function testTaskRecordsSucessFaillProcessed(){
        //create task
        $params = ["ServiceName" => __CLASS__, "TaskName"=>__FUNCTION__];
        //$response = $this->post('/tasklog', $params, []);
        $response = $this->json('POST', '/tasklog', $params);
        $response->assertStatus(200);
        // test success update
        $updateTaskResponseBody = json_decode($response->getContent(),true);
        $params['TaskLogId'] = $updateTaskResponseBody['TaskLogId'];
        $params['RecordsProcessedOK'] = 1;
        $params['RecordsProcessedFail'] = 9;
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(200);
        // test failed update
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(400);
    }

    public function testTaskInvalidRecords(){
        //create task
        $params = ["ServiceName" => __CLASS__, "TaskName"=>__FUNCTION__];
        //$response = $this->post('/tasklog', $params, []);
        $response = $this->json('POST', '/tasklog', $params);
        $response->assertStatus(200);
        // test success update
        $updateTaskResponseBody = json_decode($response->getContent(),true);
        $params['TaskLogId'] = $updateTaskResponseBody['TaskLogId'];
        $params['RecordsProcessedOK'] = 'c';
        $params['RecordsProcessedFail'] = 9;
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(500);
        // test failed update
        $updateTaskResponse =  $this->json('PUT', '/tasklog/'.$params['TaskLogId'], $params);
        $updateTaskResponse->assertStatus(500);
    }

}
