<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CRLibrato;

class LibratoController extends Controller
{
    protected $samplePayload;

    public function __construct()
    {
        $this->samplePayload = [
            'samplePayload' => [
                'body' => [
                    'app' => 'your-app-name',
                    'taskname' => 'your-task-name',
                    'name' => 'your-metric-name',
                    'value' => 0.2,
                    'tags' => ['tax-example' => 'david-jones', 'another-tag-example' => 'DTI-transform']
                ],
                'method' => "/metriclibrato",
                'http' => "put or patch",
                'sampleResponse' => [
                    'result' => true,
                    'message' => 'response message here',
                    'httpResponse' => 200,
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
        return response()->json($this->samplePayload, $this->samplePayload['httpResponse']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json($this->samplePayload, $this->samplePayload['httpResponse']);
    }

    /**
     * Store a newly created resource in storage. POST
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Now we can get the content from it
        $content = $request->getContent();
        //get the associative array
        $params = json_decode($content, true);
        $result = [];
        if(is_array($params)){

            if(array_key_exists('taskname', $params)
                && array_key_exists('value', $params) == true
                && array_key_exists('app', $params)
                && array_key_exists('metric', $params)){
                $tags = array_key_exists('tags', $params)
                    ? $params['tags']
                    : [];
                $l = new CRLibrato($params['metric'], $params['value'], $params['app'], $params['taskname'], $tags);
                $result = $l->result;

            }else{ //missing valid payload
                $result['result'] = false;
                $result['httpResponse'] = 400;
                $result['message'] = 'app, taskname, name or value is missing';
                $result['example'] = $this->samplePayload;
            }

        }
        return response()->json($result, $result['httpResponse']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json($this->samplePayload, $this->samplePayload['httpResponse']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json($this->samplePayload, $this->samplePayload['httpResponse']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return response()->json($this->samplePayload, $this->samplePayload['httpResponse']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json($this->samplePayload, $this->samplePayload['httpResponse']);
    }
}
