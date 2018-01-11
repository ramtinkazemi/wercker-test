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
    public $errorResult;

    public function __construct($paramsArr)
    {
        $this->errorResult['result'] = false;
        $this->errorResult['TaskLogId'] = "";
        $this->errorResult['httpResponse'] = 404;
        $this->errorResult['description'] = '';
        $this->errorResult['TaskLogId'] = '';

        if(!array_key_exists('TaskLogId', $paramsArr)){ // new task
            $this->result = $this->createNewTask($paramsArr);
        }else{ // update task
            $this->result = $this->updateTask($paramsArr);
        }
    }

    /**
     *
     * creates a new task
     *
     * @param $paramsArr
     * @return array
     */
    private function createNewTask($paramsArr){
        try{
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
            return $result;
        }catch(\Exception $e){
            CRLog("error", "Exception", "env:".env('APP_ENV')."\n paramsArr: ".json_encode($paramsArr).", \n".$e->getMessage().",\n". json_encode($this->getResponse("", 500, false, "")), __CLASS__, __FUNCTION__, __LINE__);
            return $this->getResponse($e->getMessage()."\n".json_encode($paramsArr), 500, false, "");
        }
    }

    /**
     *
     * gets a response
     *
     * @param $description string
     * @param $httpResponse int
     * @param $resultBool, boolean
     * @param $taskLogId, integer
     * @return array
     */
    private function getResponse($description, $httpResponse, $resultBool, $taskLogId){
        $result = $this->errorResult;
        $result['result'] = $resultBool;
        $result['description'] = $description;
        $result['httpResponse'] = $httpResponse;
        $result['TaskLogId'] = $taskLogId;
        return $result;
    }

    /**
     *
     * updates a task
     *
     * @param $paramsArr
     * @return array
     */
    private function updateTask($paramsArr){
        // get object so we can get runtime
        $taskLogId = $paramsArr['TaskLogId'];
        $tl = TaskLog::find($paramsArr['TaskLogId']);
        if(count($tl)>0){
            //check if this task is already completed
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
                    }
                }catch(\Exception $e){
                    $result = $this->getResponse('task log not found'.$e->getMessage(), 500, true, $paramsArr['TaskLogId']);
                    CRLog("error", "Exception", "Task log id : ".$paramsArr['TaskLogId'].", ".$result['httpResponse'].", ".$result['description'].", \n".$e->getMessage().",\n". json_encode($result), __CLASS__, __FUNCTION__, __LINE__);
                    return $result;
                }
                $paramsArr['Id'] = $paramsArr['TaskLogId'];
                unset($paramsArr['TaskLogId']);

                // save the request
                TaskLog::where('id', $paramsArr['Id'])
                    ->update($paramsArr);

                $result = $this->getResponse('task updated', 200, true, $taskLogId);
                CRLog("debug", "debug statement, ".$result['httpResponse'].", ".$result['description'], json_encode($result), __CLASS__, __FUNCTION__, __LINE__);
            }else{
                $result = $this->getResponse('task was already completed at '.$tl->updated_at, 400, false, $paramsArr['TaskLogId']);
                CRLog("debug", "debug statement: ".$result['httpResponse'].", ".$result['description'], json_encode($result), __CLASS__, __FUNCTION__, __LINE__);
            }
        }else{
            $result = $this->getResponse('task not found', 404, false, $paramsArr['TaskLogId']);
            CRLog("debug", "debug statement: ".$result['httpResponse'].", ".$result['description'], json_encode($result), __CLASS__, __FUNCTION__, __LINE__);
        }
        return $result;
    }
}