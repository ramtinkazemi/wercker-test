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

    public function __Construct($service){
        CRLog("debug", "update CYFE $service", "", __CLASS__, __FUNCTION__, __LINE__);
        $this->result = [];
        $this->service = $service;
        $this->getToday();
        $this->getPreviousDay();
        $this->getLastSevenDays();
        $this->addMissingKeys();
        $this->csv = $this->getCSV();
        $this->saveS3();
    }

    private function getToday(){
        $sql = "SELECT ServiceName, TaskName, COUNT(*)  as Executions, AVG(DurationSeconds) as AverageDuration, SUM(RecordsProcessed) as RecordsProcessed, TaskComplete FROM TaskLog WHERE created_at >= CURRENT_DATE() AND TaskComplete = 1 AND ServiceName = '".$this->service."' GROUP BY ServiceName, TaskName, TaskComplete ORDER BY ServiceName, TaskName, TaskComplete";
        $rs = DB::SELECT($sql);
        $this->result['today'] = $this->getArray($rs);
        //print_r($this->result);
    }

    private function getPreviousDay(){
        $sql = "SELECT ServiceName, TaskName, COUNT(*)  as Executions, AVG(DurationSeconds) as AverageDuration, SUM(RecordsProcessed) as RecordsProcessed, TaskComplete FROM TaskLog WHERE created_at >= CURRENT_DATE()-1 AND created_at < CURRENT_DATE() AND TaskComplete = 1 AND ServiceName =  '".$this->service."' GROUP BY ServiceName, TaskName, TaskComplete ORDER BY ServiceName, TaskName, TaskComplete";
        $rs = DB::SELECT($sql);
        $this->result['yesterday'] = $this->getArray($rs);
    }

    private function getLastSevenDays(){
        $sql = "SELECT ServiceName, TaskName, COUNT(*)  as Executions, AVG(DurationSeconds) as AverageDuration, SUM(RecordsProcessed) as RecordsProcessed, TaskComplete FROM TaskLog WHERE created_at >= CURRENT_DATE()-7  AND TaskComplete = 1 AND ServiceName =  '".$this->service."' GROUP BY ServiceName, TaskName, TaskComplete ORDER BY ServiceName, TaskName, TaskComplete";
        $rs = DB::SELECT($sql);
        $this->result['last-seven-days'] = $this->getArray($rs);
    }

    private function deleteOver7days(){

    }

    private function getArray($object){
        $result = [];
        foreach($object as $key=>$serviceTask){
            $result[$serviceTask->ServiceName.'-'.$serviceTask->TaskName] = json_decode(json_encode($serviceTask), true);
        }
        return $result;
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
        $task['Executions'] = 0;
        $task['AverageDuration'] = 0;
        $task['RecordsProcessed'] = 0;
        $task['TaskComplete'] = 0;
        return $task;
    }

    private function getCSV(){
        $arr[] = [
            'Task name',
            'Executions today',
            'AVG duration today',
            'Records processed today',
            'Executions yesterday',
            'AVG duration yesterday',
            'Records processed yesterday',
            'Executions last 7 days',
            'AVG duration last 7 days',
            'Records processed last 7 days',
            'Incomplete tasks last 7 days',
        ];
        foreach($this->result['last-seven-days'] as $key=>$task){
            $arr[] = [$key,
                $this->result['today'][$key]['Executions'],
                $this->result['today'][$key]['AverageDuration'],
                $this->result['today'][$key]['RecordsProcessed'],
                $this->result['yesterday'][$key]['Executions'],
                $this->result['yesterday'][$key]['AverageDuration'],
                $this->result['yesterday'][$key]['RecordsProcessed'],
                $this->result['last-seven-days'][$key]['Executions'],
                $this->result['last-seven-days'][$key]['AverageDuration'],
                $this->result['last-seven-days'][$key]['RecordsProcessed'],
                0 //@todo  incompete tasks
            ];
        }
        $csv = [];
        foreach($arr as $key=>$line){
            $csv[] = implode(",", $line);
        }
        return implode("\n", $csv);
    }

    /**
     * save file to S3
     */
    private function saveS3(){
        Storage::disk('s3')->put("cyfe/".$this->service.".csv", $this->csv);
    }
}