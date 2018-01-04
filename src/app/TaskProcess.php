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
            $paramsArr['LastRunAT'] = Carbon::now()->toDateTimeString();

            $tl = TaskLog::create($paramsArr);
            $paramsArr['TaskLogId'] = $tl->id;

            $t = Task::updateOrCreate(['TaskName'=> $paramsArr['TaskName'], 'ServiceName' => $paramsArr['ServiceName']], $paramsArr);

            //add key to task log
            $tl->TaskId = $t->id;
            $tl->save();

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
            if(count($tl)>0){
                if($tl->TaskComplete == false){
                    // get timings
                    $startTime = Carbon::parse($tl->created_at);
                    $finishTime =  Carbon::now();
                    $paramsArr['DurationSeconds'] = $finishTime->diffInSeconds($startTime);
                    $paramsArr['TaskComplete'] = true;
                    try{
                        $t = Task::where('ServiceName',  '=' , $paramsArr['ServiceName'])->where('TaskName', '=' ,$paramsArr['TaskName'])->first();
                        if(count($t) > 0){
                            $t->RecordsProcessed = $paramsArr['RecordsProcessed'];
                            $t->TaskComplete = true;
                            $t->TaskLogId = $paramsArr['TaskLogId']; // since any of the tasks can complete first lets make sure we capture which one was the completed one
                            $t->DurationSeconds = $paramsArr['DurationSeconds'];
                            $t->RecordsProcessedOK = $paramsArr['RecordsProcessedOK'];
                            $t->RecordsProcessedFail = $paramsArr['RecordsProcessedFail'];
                            $t->save();
                            $paramsArr['TaskId'] = $t->id;
                        }else{
                            $result['result'] = true;
                            $result['httpResponse'] = 404;
                            $result['description'] = 'task not found';
                        }
                    }catch(Exception $e){

                        $result['result'] = true;
                        $result['httpResponse'] = 404;
                        $result['description'] = 'task log not found'.$e->getMessage();
                        CRLog("debug", "debug statement, ".$result['httpResponse'].", ".$result['description'].", ".$e->getMessage(), json_encode($result), __CLASS__, __FUNCTION__, __LINE__);
                    }


                    $paramsArr['Id'] = $paramsArr['TaskLogId'];
                    unset($paramsArr['TaskLogId']);

                    // save the request
                    TaskLog::where('id', $paramsArr['Id'])
                        ->update($paramsArr);
                    $result['result'] = true;
                    $result['httpResponse'] = 200;
                    $result['description'] = 'task updated';
                    CRLog("debug", "debug statement, ".$result['httpResponse'].", ".$result['description'], json_encode($result), __CLASS__, __FUNCTION__, __LINE__);
                }else{
                    $result['result'] = false;
                    $result['httpResponse'] = 400;
                    $result['description'] = 'task was already completed at '.$tl->updated_at;
                    CRLog("debug", "debug statement: ".$result['httpResponse'].", ".$result['description'], json_encode($result), __CLASS__, __FUNCTION__, __LINE__);
                }
            }else{

                $result['result'] = false;
                $result['httpResponse'] = 404;
                $result['description'] = 'task not found';
                CRLog("debug", "debug statement: ".$result['httpResponse'].", ".$result['description'], json_encode($result), __CLASS__, __FUNCTION__, __LINE__);
            }

        }
        $this->result = $result;
    }

}