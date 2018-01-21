<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LibratoControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreate()
    {

       // $payload = json_decode('{ "": "", "taskname": "", "": ", "": 0.2, "": { , "another-tag-example": "DTI-transform" }', true);
        //create task
        $params = [
            "app" => 'your-app-name',
            "taskname"=>'your-task-name',
            "metric"=>"your-metric-name",
            "value" => 0.2,
            "tags" => [
                "tax-example" => "david-jones"
            ]
        ];
        $response = $this->json('POST', '/metriclibrato', $params);
        echo "\n\n".$response->getContent()."\n\n";
        $response->assertStatus(202);
    }
}
