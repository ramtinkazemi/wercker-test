<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 19/12/2017
 * Time: 12:24
 */

namespace App;
use Carbon\Carbon;

class TaskProcess
{
    public $result;

    public function __construct($paramsArr)
    {
        $result['result'] = false;
        $result['TaskLogId'] = "";
        $result['httpResponse'] = 404;
        $result['description'] = '';

        if(!array_key_exists('TaskLogId', $paramsArr)){ // new task

            $paramsArr['TaskComplete'] = false;
            $paramsArr['RecordsProcessed'] = 0;
            $paramsArr['DurationSeconds'] = 0;

            $tl = TaskLog::create($paramsArr);
            $paramsArr['TaskLogId'] = $tl->id;

            $t = Task::updateOrCreate(['TaskName'=> $paramsArr['TaskName'], 'ServiceName' => $paramsArr['ServiceName']], $paramsArr);

            //update the result
            $result['result'] = true;
            $result['TaskLogId'] = $tl->id;
            $result['httpResponse'] = 200;
            $result['ServiceName'] = $tl->ServiceName;
            $result['TaskName'] = $tl->TaskName;
            $result['description'] = 'task created';
        }else{

            //check if this task is already completed

            // we got an id so we got to update an existing task
            // get object so we can get runtime

            $result['TaskLogId'] = $paramsArr['TaskLogId'];
            $tl = TaskLog::find($paramsArr['TaskLogId']);
            if($tl->TaskComplete == false){
                // get timings
                $startTime = Carbon::parse($tl->created_at);
                $finishTime =  Carbon::now();

                $paramsArr['DurationSeconds'] = $finishTime->diffInSeconds($startTime);
                $paramsArr['TaskComplete'] = true;

                $t = Task::updateOrCreate(['TaskName'=> $paramsArr['TaskName'], 'ServiceName' => $paramsArr['ServiceName']], $paramsArr);

                $paramsArr['Id'] = $paramsArr['TaskLogId'];
                unset($paramsArr['TaskLogId']);

                // save the request
                //print_r($paramsArr);die;
                TaskLog::where('id', $paramsArr['Id'])
                    ->update($paramsArr);
                $result['result'] = true;
                $result['httpResponse'] = 200;
                $result['description'] = 'task updated';
            }else{
                $result['result'] = false;
                $result['httpResponse'] = 400;
                $result['description'] = 'task was already completed at '.$tl->updated_at;
            }
        }
        $this->result = $result;
    }

}