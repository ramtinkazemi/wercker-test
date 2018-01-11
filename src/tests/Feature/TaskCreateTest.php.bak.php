<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;

/**
 *
 * Integration tests
 * WARNING it uses the local env file not the the testing one as it makes an http call
 *
 * Class TaskCreateTest
 * @package Tests\Feature
 */
class TaskCreateTest extends TestCase
{

    /**
     *  case 1 - no records processed
     *
     * @return void
     */
    public function testIntegrationCreateTask()
    {
        // test creating a new task
        $params = ["ServiceName" => 'crutils', "TaskName"=>'crutils'];
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog'], "post");
        $this->checkResult($params, $result, "post", 200, true);
       // echo "params 1\n";print_r($result);

        //test update without the records processed
        $params['TaskLogId'] = $result['TaskLogId'];
        //echo "result 1\n";print_r($params);

        // test we can update the task
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog/'.$params['TaskLogId']], "put");
        //echo "result 2\n";print_r($result);
        $this->checkResult($params, $result, "put", 200, true);

        //test we cannot update the task already saved
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog/'.$params['TaskLogId']], "put");
        //echo "result 3\n";print_r($result);
        $this->checkResult($params, $result, "put", 400, false);

    }

    /**
     * case 2 - only with total records processed
     */
    public function testIntegrationTaskTotalRecordsOnly(){
        // create new task
        $params = ["ServiceName" => __CLASS__, "TaskName"=>__FUNCTION__];
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog'], "post");
        $this->checkResult($params, $result, "post", 200, true);
        //echo "params 1\n";print_r($result);

        // test we can update the task with records processed
        $params['TaskLogId'] = $result['TaskLogId'];
        $params['RecordsProcessed'] = 5;
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog/'.$params['TaskLogId']], "put");
        //echo "result 2\n";print_r($result);
        $this->checkResult($params, $result, "put", 200, true);

        //test we cannot update the task already saved
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog/'.$params['TaskLogId']], "put");
        //echo "result 3\n";print_r($result);
        $this->checkResult($params, $result, "put", 400, false);
    }



    /**
     * case 3 - with total records processed and success/fail
     */
    public function testIntegrationTaskFullRecordsProcessed(){
        $params = ["ServiceName" => __CLASS__, "TaskName"=>__FUNCTION__];
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog'], "post");
        $this->checkResult($params, $result, "post", 200, true);
       // echo "params 1\n";print_r($result);

        // test we can update the task with records processed
        $params['TaskLogId'] = $result['TaskLogId'];
        $params['RecordsProcessed'] = 5;
        $params['RecordsProcessedOK'] = 1;
        $params['RecordsProcessedFail'] = 4;

        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog/'.$params['TaskLogId']], "put");
        //echo "result 2\n";print_r($result);
        $this->checkResult($params, $result, "put", 200, true);

        //test we cannot update the task already saved
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog/'.$params['TaskLogId']], "put");
       // echo "result 3\n";print_r($result);
        $this->checkResult($params, $result, "put", 400, false);
    }

    /**
     * case 4 - without  total records processed and success/fail
     */
    public function testIntegrationTaskSuccessFailRecordsProcessed(){
        $params = ["ServiceName" => __CLASS__, "TaskName"=>__FUNCTION__];
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog'], "post");
        $this->checkResult($params, $result, "post", 200, true);
        //echo "params 1\n";print_r($result);

        // test we can update the task with records processed
        $params['TaskLogId'] = $result['TaskLogId'];
        $params['RecordsProcessedOK'] = 1;
        $params['RecordsProcessedFail'] = 4;

        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog/'.$params['TaskLogId']], "put");
        //echo "result 2\n";print_r($result);
        $this->checkResult($params, $result, "put", 200, true);

        //test we cannot update the task already saved
        $result = $this->CRmetric($params, ['crmetricsMethod' => 'tasklog/'.$params['TaskLogId']], "put");
        //echo "result 3\n";print_r($result);
        $this->checkResult($params, $result, "put", 400, false);
    }

    private function checkResult($params, $result, $method, $expectedHttpResponse, $resultExpectedBool){
        if($method == "post"){
            $this->assertEquals($params['ServiceName'], $result['ServiceName']);
            $this->assertEquals($params['TaskName'], $result['TaskName']);
        }

        $this->assertEquals($result['httpResponse'], $expectedHttpResponse);
        $this->assertEquals($result['result'], $resultExpectedBool);
    }



    private function CRmetric($params, $options, $method)
    {
        try {
            $postData = json_encode($params, JSON_OBJECT_AS_ARRAY);
            $uri = $uri = env('CRUTILS_METRICS_URI', 'http://127.0.0.1:81/') . $options['crmetricsMethod'];
            //echo "$uri\n";
            $client = new GuzzleHttp\Client();
            $tarr = [
                'body' => $postData,
                'allow_redirects' => false,
                'timeout' => 60
            ];
            if($method == "post"){
                $response = $client->post($uri, $tarr);
            }else{
                $response = $client->put($uri, $tarr);
            }

            $result = json_decode($response->getBody(), true);
            return $result;
        }
        catch (RequestException $e) {
            return json_decode($e->getResponse()->getBody(), true);
        }
        catch (\Exception $e) {
            CRLog("error", "exception", $e->getMessage(), __CLASS__, __FUNCTION__, __LINE__);
            return json_decode($response->getBody(), true);
        }
    }
}
