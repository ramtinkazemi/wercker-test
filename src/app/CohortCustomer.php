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

    private function getAll($type){
        $key = "membersCohort";

        $data = Cache::remember($key, 720, function () {
            $sql = "SET NOCOUNT ON; EXEC Report_GetMemberTransActivitySummary";
            $dbh = DB::connection('sqlsrv')->getPdo();
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $data = array();
            $data['tr5savings'] = $sth->fetchAll(\PDO::FETCH_CLASS);
            $sth->nextRowset();
            $data['noTR5savings'] = $sth->fetchAll(\PDO::FETCH_CLASS);
            return $data;
        });


        foreach($data['tr5savings'] as $key=>$row){
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

        $lines[] = $headers;  //first line of the csv
        foreach($this->membersAll as $memberJoined=>$row){

            $lineItem = [];
            $lineItem[] = $memberJoined;
            $lineItem[] = 0;

            // process transaction segments
            $totalrow = 0;
            foreach($row as $transactionDate=>$counts){

                if(!array_key_exists('MemberCountActive', $counts)){
                    $lineItem[] = 0;
                }else{
                    $lineItem[] = $counts['MemberCountActive'];
                    $totalrow = $totalrow + $counts['MemberCountActive'];
                }
            }

            if($type == "active"){
                $lineItem[1] = $totalrow;
            }elseif($type == "all"){
                $lineItem[1] = $this->membersAllNotTransacted[$memberJoined] + $totalrow;
            }
            $lines[] = $lineItem;
        }

        foreach($lines as $key=>$lineOne){
            $a=0;
           while($a<16){
                if(!array_key_exists($a, $lineOne)){
                    $lineOne[$a] = 0;
                }
               $a++;
           }
           $lines[$key] = implode(",", $lineOne);

        }if(env('APP_ENV') == "prod"){ //only here publish
            Storage::disk('s3')->put("cyfe/customer-cohort-$type.csv", implode("\n", $lines));
        }else{
            echo implode("\n", $lines)."\n";
        }
        $this->csv[$type] = implode("\n", $lines);
    }



}