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
use App\TaskLog;
use Carbon\Carbon;

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
    public $csvSummary; // Service task summary

    public function __Construct($service){
        CRLog("debug", "update CYFE $service", "", __CLASS__, __FUNCTION__, __LINE__);
        $this->allTasksForService = [];

        $this->result = [];
        $this->service = $service;
        $this->getAllTasksForService();
        $this->getAllExecutions();
        $this->addMissingKeys();
        $this->csv = $this->getCSV();
        $this->csvSummary = $this->getCSVSummary();
        $this->saveS3();
        $this->deleteOver7days();
    }

    /**
     *
     * get the execution for a number of days back
     *
     * @param $daysBack
     * @return array
     */
    private function getExecutionsForDays($daysBack)
    {
        if ($daysBack == 1 || $daysBack == 7) { //yesterday
            $daysBack = "AND created_at < CURRENT_DATE() AND created_at >= CURRENT_DATE() -$daysBack ";
        }elseif($daysBack == 0) {
            $daysBack = "AND created_at >= CURRENT_DATE()";
        }else{ //today and last seven days
          $daysBack = "AND created_at >= CURRENT_DATE() -$daysBack";
        }
        $result = [];
        foreach($this->allTasksForService as $taskKey=>$task){
            $result[$taskKey] = ['task-1' => 0, 'task-0' => 0];
            $sql = "SELECT TaskComplete, count(*) as total FROM TaskLog Where TaskId = ".$task['id']." $daysBack GROUP BY TaskComplete;";
            $rs = DB::SELECT($sql);
            foreach($rs as $key=>$value){
                $result[$taskKey]['task-'.$value->TaskComplete] = $value->total;
            }
            $result[$taskKey]['total'] = $result[$taskKey]['task-1'] + $result[$taskKey]['task-0'];
        }
        return $result;
    }

    /**
     * gets all executions for the date ranges of interest
     */
    private function getAllExecutions(){
        $days = ['today'=>0, 'yesterday'=>1, 'last-seven-days'=>7];
        foreach($days as $description=>$daysBack){
            $this->result[$description] = $this->getExecutionsForDays($daysBack);
        }
    }

    /**
     * delete old records
     */
    private function deleteOver7days(){
        $deletedRows = TaskLog::where('created_at', '<', Carbon::now()->subDays(8))->delete();
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
            $serviceArr = explode("@@@", $key);
           // $key = str_replace($key, "@@@", ":");
            $arr[] = [
                str_replace("@@@", "-", $key),   // 0
                $this->result['today'][$key]['total'],          // 1
                $this->result['today'][$key]['task-1'],         // 2
                $this->result['today'][$key]['task-0'],         // 3
                $this->result['yesterday'][$key]['total'],      // 4
                $this->result['yesterday'][$key]['task-1'],     // 5
                $this->result['yesterday'][$key]['task-0'],     // 6
                $this->result['last-seven-days'][$key]['total'], //7
                $this->result['last-seven-days'][$key]['task-1'],//8
                $this->result['last-seven-days'][$key]['task-0'],//9
            ];
        }
        $csv = ['all' => [], 'incomplete-today'=>[], 'incomplete-yesterday' => [], 'incomplete-last-seven-days' => []];

        foreach($arr as $key=>$line){
            $csv['all'][] = implode(",", $line);
            if($line[4] != $line[5] || $line[4] == 0){ // incomplete or no executions yesterday
                $csv['incomplete-yesterday'][] = implode(",", $line);
            }
            if($line[7] != $line[8] || $line[7] == 0){ // incomplete or no complete in last seven days
                $csv['incomplete-last-seven-days'][] = implode(",", $line);
            }
            if($line[1] == 0){ // no executions or no complete in last seven days
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
     *
     * get csv summary for tasks complete/incomplete
     *
     * @return array
     */
    private function getCSVSummary(){
        $arrKeys = [
            'Complete',
            'Incomplete',
        ];

        $csvPeriods = ['today',  'yesterday', 'last-seven-days'];

        foreach($csvPeriods as $period){
            //$arr = [];
            $arr[$period][] = $arrKeys;
            foreach($this->result[$period] as $key=>$task){
                $serviceArr = explode("@@@", $key);
                if(!array_key_exists($key, $arr[$period])){ //add zero values if new row
                    $arr[$period][$serviceArr[0]][] = 0;
                    $arr[$period][$serviceArr[0]][] = 0;
                }
                $arr[$period][$serviceArr[0]] = [
                        ($task['task-1'] + $arr[$period][$serviceArr[0]][0]),
                        ($task['task-0'] + $arr[$period][$serviceArr[0]][1]),
                    ];
            }
        }

        $lines = [];
        foreach($csvPeriods as $period){
            $lines[$period] = [];
            foreach($arr[$period] as $key=>$line){
                $lines[$period][] = implode(",", $line);
            }
            $lines[$period] = implode("\n", $lines[$period]);
        }
        return $lines;
    }

    /**
     * save file to S3
     */
    private function saveS3(){
        if(env('APP_ENV') != 'testing'){ //only save when not unit testing
            $csv = ['all', 'incomplete-today', 'incomplete-yesterday', 'incomplete-last-seven-days'];
            foreach($csv as $csvType){
                Storage::disk('s3')->put("cyfe/".$this->service."-$csvType.csv", $this->csv[$csvType]);
            }
            foreach(['today',  'yesterday', 'last-seven-days'] as $csvType){
                Storage::disk('s3')->put("cyfe/".$this->service."-summary-$csvType.csv", $this->csvSummary[$csvType]);
            }
        }else{
            CRLog("debug", "Not saving to S3", "", __CLASS__, __FUNCTION__, __LINE__);
        }
    }

    /**
     * returns all tasks for all services regardless of recent tasks
     */
    private function getAllTasksForService(){
        $sql = "Select * FROM Task Where ServiceName = '".$this->service."'";
        $rs = DB::SELECT($sql);
        foreach($rs as $key=>$value){
            $this->allTasksForService[$value->ServiceName."@@@".$value->TaskName]['id'] = $value->id;
        }
    }
}