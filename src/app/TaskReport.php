<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 01/01/2018
 * Time: 21:17
 */

namespace App;
use DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class TaskReport
 *
 * Returns summary task metrics
 *
 * @package App
 */
class TaskReport
{
    public $service;
    public $result;
    public $csv;
    public $allTasksForService; // array of all tasks of service in Task Table

    public function __Construct($service){
        CRLog("debug", "update CYFE $service", "", __CLASS__, __FUNCTION__, __LINE__);
        $this->allTasksForService = [];

        $this->result = [];
        $this->service = $service;
        $this->getAllTasksForService();
        $this->getAllExecutions();
        $this->addMissingKeys();
        $this->csv = $this->getCSV();
        $this->saveS3();
    }

    private function getExecutionsForDays($daysBack){
        $result = [];
        foreach($this->allTasksForService as $taskKey=>$task){
            $result[$taskKey] = ['task-1' => 0, 'task-0' => 0];
            $sql = "SELECT TaskComplete, count(*) as total FROM TaskLog Where TaskId = ".$task['id']." AND created_at >= CURRENT_DATE() -$daysBack GROUP BY TaskComplete;";
            $rs = DB::SELECT($sql);
            foreach($rs as $key=>$value){
                $result[$taskKey]['task-'.$value->TaskComplete] = $value->total;
            }
            $result[$taskKey]['total'] = $result[$taskKey]['task-1'] + $result[$taskKey]['task-0'];
        }
        return $result;
    }

    private function getAllExecutions(){
        $days = ['today'=>0, 'yesterday'=>1, 'last-seven-days'=>7];
        foreach($days as $description=>$daysBack){
            $this->result[$description] = $this->getExecutionsForDays($daysBack);
        }
    }

    private function deleteOver7days(){ //@todo

    }

    /**
     * make sure all kays are in all arrays and show task as zero
     */
    private function addMissingKeys(){
        foreach($this->result['today']  as $key=>$task){
            if(!array_key_exists($key, $this->result['last-seven-days'])){
                $this->result['last-seven-days'][$key] = $this->getBlankTask($task);
            }
            if(!array_key_exists($key, $this->result['yesterday'])){
                $this->result['yesterday'][$key] = $this->getBlankTask($task);
            }
        }
        foreach($this->result['yesterday']  as $key=>$task){
            if(!array_key_exists($key, $this->result['last-seven-days'])){
                $this->result['last-seven-days'][$key] = $this->getBlankTask($task);
            }
            if(!array_key_exists($key, $this->result['today'])){
                $this->result['today'][$key] = $this->getBlankTask($task);
            }
        }
        foreach($this->result['last-seven-days'] as $key=>$task){
            if(!array_key_exists($key, $this->result['today'])){
                $this->result['today'][$key] = $this->getBlankTask($task);
            }
            if(!array_key_exists($key, $this->result['yesterday'])){
                $this->result['yesterday'][$key] = $this->getBlankTask($task);
            }
        }

    }

    /**
     *
     * return an empty task
     *
     * @param $task
     * @return mixed
     */
    private function getBlankTask($task){
        $task['total'] = 0;
        $task['task-1'] = 0;
        $task['task-0'] = 0;
        return $task;
    }


    /**
     *
     * produce csv files (all, incomplete or no complete yesterday, incomplete or no complete in last seven days
     *
     * @return array
     */
    private function getCSV(){
        $arr[] = [
            'Task name',
            'Today total',
            'Today complete',
            'Today incomplete',
            'yesterday total',
            'yesterday complete',
            'yesterday incomplete',
            '7 days total',
            '7 days complete',
            '7 daysincomplete',
        ];
        foreach($this->result['last-seven-days'] as $key=>$task){
            $arr[] = [$key,
                $this->result['today'][$key]['total'],
                $this->result['today'][$key]['task-1'],
                $this->result['today'][$key]['task-0'],
                $this->result['yesterday'][$key]['total'],
                $this->result['yesterday'][$key]['task-1'],
                $this->result['yesterday'][$key]['task-0'],
                $this->result['last-seven-days'][$key]['total'],
                $this->result['last-seven-days'][$key]['task-1'],
                $this->result['last-seven-days'][$key]['task-0'],
            ];
        }
        $csv = ['all' => [], 'incomplete-today'=>[], 'incomplete-yesterday' => [], 'incomplete-last-seven-days' => []];

        foreach($arr as $key=>$line){
            $csv['all'][] = implode(",", $line);
            if($line[4] != $line[6] || $line[5] == 0){ // incomplete or no complete yesterday
                $csv['incomplete-yesterday'][] = implode(",", $line);
            }
            if($line[7] != $line[9] || $line[8] == 0){ // incomplete or no complete in last seven days
                $csv['incomplete-last-seven-days'][] = implode(",", $line);
            }
            if($line[1] == 0 || $line[2] == 0){ // no executions or no complete in last seven days
                $csv['incomplete-today'][] = implode(",", $line);
            }
        }
        //get each array in csv format
        foreach($csv as $key=>$value){
            $csv[$key] = implode("\n",$csv[$key]);
        }
        return $csv;
    }

    /**
     * save file to S3
     */
    private function saveS3(){
        $csv = ['all', 'incomplete-today', 'incomplete-yesterday', 'incomplete-last-seven-days'];
        foreach($csv as $csvType){
            Storage::disk('s3')->put("cyfe/".$this->service."-$csvType.csv", $this->csv[$csvType]);
        }
    }

    private function getAllTasksForService(){
        $sql = "Select * FROM Task Where ServiceName = '".$this->service."'";
        $rs = DB::SELECT($sql);
        foreach($rs as $key=>$value){
            $this->allTasksForService[$value->ServiceName."-".$value->TaskName]['id'] = $value->id;
        }
    }
}