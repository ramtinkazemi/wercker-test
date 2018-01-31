<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 29/01/2018
 * Time: 16:27
 */

namespace App;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CohortCustomer
{
    public $membersAll;
    public $membersAllNotTransacted; // no TR5 or savings ever
    public $periods;
    public $csv;

    public function __construct()
    {

        $this->membersAll = [];
        $this->getAll("active");
        $this->getAll("all");
    }


    private function getMemberCountsByPeriod(){
        $nr = [];
        $sql  = "SELECT datepart(year, DateJoined) as year, datepart(month, DateJoined) as month, COUNT(*) as total From dbo.Member GROUP BY datepart(year, DateJoined), datepart(month, DateJoined);";
        $rsArr = DB::connection('sqlsrv')->select($sql);
        foreach($rsArr as $key=>$row){
            $nr[$row->year."-".$row->month]['total'] = $row->total;
        }
        $this->membersAll = $nr;
        $this->processSegments();
    }

    private function processSegments(){
        foreach($this->membersAll as $segment=> $val){
            $this->getMemberSegment($segment);
        }
    }

    /**
     * get date periods for sales
     */
    private function setPeriods(){
        $max = 0;
        $start = Carbon::now()->format('Y-m');
        while($max < 12){
            $this->periods[$max]['from'] = $start."-01";
            $this->periods[$max]['to'] = Carbon::parse($start)->addMonth(1)->format('Y-m-d');
            $max = $max+1;
        }
    }



    private function getAll($type){
        $key = "membersCohort";

        //Cache::forget($key);

        $data = Cache::remember($key, 720, function () {
            $sql = "SET NOCOUNT ON; EXEC Report_GetMemberTransActivitySummary";
            $dbh = DB::connection('sqlsrv')->getPdo();
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $data['tr5savings'] = $sth->fetchAll(\PDO::FETCH_CLASS);
            //print_r($sth->fetchAll(\PDO::FETCH_CLASS));die;
            $sth->nextRowset();
            $data['noTR5savings'] = $sth->fetchAll(\PDO::FETCH_CLASS);
            return $data;
        });


        foreach($data['tr5savings'] as $key=>$row){
           // $dataArr  = explode("/", $row->MemberJoinMonth);
            if($row->Active == 1){
                $this->membersAll[$row->MemberJoinMonth][$row->TransactionMonth] ['MemberCountActive']= $row->MemberCount;
            }else{
                $this->membersAll[$row->MemberJoinMonth][$row->TransactionMonth] ['MemberCountInActive'] = $row->MemberCount;
            }
        }

        // get the members that have not transacted

        foreach($data['noTR5savings'] as $key=>$row){
            $this->membersAllNotTransacted[$row->MemberJoinMonth] = $row->MemberCount;
        }

        $this->getCSV($type);
    }

    /**
     * prepare the csv file
     */
    private function getCSV($type){

        $lines = [];

        $headers = [];
        foreach($this->membersAll as $memberJoined=>$row){

            foreach($row as $transactionDate=>$counts){
                if(!in_array($transactionDate, $headers)){
                    $headers[] = $transactionDate;
                }
            }
        }
        if($type == "active" || $type == "all") {
            array_unshift($headers, "date joined", "total");
        }

        $lines[] = $headers;//implode(",", $headers); //fisrt line of the csv
        foreach($this->membersAll as $memberJoined=>$row){
            $totalrow = 0;
            $line = [];
            $line[] = $memberJoined;
            $line[] = 0;
            foreach($row as $transactionDate=>$counts){
                if(!array_key_exists('MemberCountActive', $counts)){
                    $line[] = 0;
                }else{
                    $line[] = $counts['MemberCountActive'];
                    $totalrow = $totalrow + $counts['MemberCountActive'];
                }
                if($type == "active"){

                }elseif($type == "all"){
                    $line[1] = $this->membersAllNotTransacted[$memberJoined];
                }
            }
            $lines[] = $line;
        }

        foreach($lines as $key=>$line){
            $a=0;
           while($a<15){
                if(!array_key_exists($a, $line)){
                    $line[$a] = 0;
                }
               $a++;
           }
           $lines[$key] = implode(",", $line);

        }
        Storage::disk('s3')->put("cyfe/customer-cohort-$type.csv", implode("\n", $lines));
        $this->csv[$type] = implode("\n", $lines);
    }



}