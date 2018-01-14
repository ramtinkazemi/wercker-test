<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TaskProcess;

class TaskLog extends Controller
{

    public $samplePayload;

    public function __construct()
    {
        $this->samplePayload = [
            'samplePayloadCompletion' => [
                'body' => [
                    'ServiceName' => 'your-service-name',
                    'TaskName' => 'your-task-name',
                    'TaskLogId' => 000000000,
                    'RecordsProcessed' => 1,
                    'RecordsProcessedOK' => 1,
                    'RecordsProcessedFail' => 1
                ],
                'method' => "/tasklog",
                'http' => "put or patch",
                'sampleResponse' => [
                    'result' => true,
                    'TaskLogId' => 000000000,
                    'httpResponse' => 200,
                    'ServiceName' => 'your-service-name',
                    'TaskName' => 'your-task-name'
                ]
            ],
            'samplePayloadStart' => [
                'body' => [
                    'ServiceName' => 'your-service-name',
                    'TaskName' => 'your-task-name'
                ],
                'method' => "/tasklog",
                'http' => "post",
                'sampleResponse' => [
                    'result' => true,
                    'TaskLogId' => 000000000,
                    'httpResponse' => 200,
                    'ServiceName' => 'your-service-name',
                    'TaskName' => 'your-task-name'
                ]
            ]
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['result'] = false;
        $data['message'] = "Method not implemented";
        $data['samplePayload'] = $this->samplePayload;
        return response()->json($data, 400);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json([], 400);
    }

    /**
     * Store a newly created resource in storage. POST
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // First we fetch the Request instance
        //$request = Request::instance();

        // Now we can get the content from it
        $content = $request->getContent();

        //get the associative array
        $params = json_decode($content, true);

        // save the request
        if(is_array($params)){
            if(array_key_exists('ServiceName', $params) && array_key_exists('TaskName', $params)){
                $tp = new TaskProcess($params);
                return response()->json($tp->result, $tp->result['httpResponse']);
            }else{
                $result = [
                    'result' => false,
                    'description'=> 'invalid payload',
                    'message' => $content,
                    'samplePayload' => $this->samplePayload
                ];
                return response()->json($result, 400);
            }
        }else{
            $result = [
                'result' => false,
                'description'=> 'invalid payload',
                'message' => $content,
                'samplePayload' => $this->samplePayload
            ];
            return response()->json($result, 400);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json([$id], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response 
     */
    public function edit($id)
    {
        return response()->json([$id], 400);
    }

    /**
     * Update the specified resource in storage. PUT/PATCH
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id,  task log id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Now we can get the content from it
        $content = $request->getContent();

        //get the associative array
        $params = json_decode($content, true);
        if(is_array($params)) {
            if (array_key_exists('ServiceName', $params) && array_key_exists('TaskName', $params)) {
                // add optional parameters
                if(!array_key_exists('RecordsProcessed', $params)){
                    $params['RecordsProcessed'] = 0;
                }
                if(!array_key_exists('RecordsProcessedOK', $params)){
                    $params['RecordsProcessedOK'] = 0;
                }
                if(!array_key_exists('RecordsProcessedFail', $params)){
                    $params['RecordsProcessedFail'] = 0;
                }
                // save the request
                $tp = new TaskProcess($params);
                return response()->json($tp->result, $tp->result['httpResponse']);
            }else{
                $result = [
                    'result' => false,
                    'description'=> 'invalid payload',
                    'message' => $content,
                    'samplePayload' => $this->samplePayload
                ];
                return response()->json($result, 400);
            }
        }else{
            $result = [
                'result' => false,
                'description'=> 'invalid payload',
                'message' => $content,
                'samplePayload' => $this->samplePayload
            ];
            return response()->json($result, 400);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json([$id], 400);
    }


}
