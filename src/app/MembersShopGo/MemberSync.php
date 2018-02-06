<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 31/01/2018
 * Time: 20:14
 */



namespace App\MembersShopGo;

use DB;
use App\MembersShopgo\MemberAggregates;
use Carbon\Carbon;

class MemberSync
{
    public function __construct($paramsArr){
        $this->syncAll();
    }


    public function syncAll(){
        echo "starting sync all \n";
        $sql = "SELECT top 1000 a.MemberId as MemberId, (Select max(SaleDate) from dbo.[Transaction] Where MemberId = a.MemberId) as LatestTr5Date,  (Select max(ActionDate) from dbo.[Savings] Where MemberId = a.MemberId) as LatestSavingsDate,  (Select max(DateCreated) from dbo.[MemberClicks] Where MemberId = a.MemberId) as LastClickDate, a.ClientId as ClientId FROM Member a Order by MemberId asc;";
        $dbresults = DB::connection('sqlsrv')->select($sql);
        //echo "found ".count($dbresults)."\n";
        while(count($dbresults > 0)){
            $lastMemberId = 0;
            foreach($dbresults as $key=>$row){
                $this->setMemberAggregate($row);
                $lastMemberId = $row->MemberId;
            }
            $sql = "SELECT top 1000 a.MemberId as MemberId, (Select max(SaleDate) from dbo.[Transaction] Where MemberId = a.MemberId) as LatestTr5Date,  (Select max(ActionDate) from dbo.[Savings] Where MemberId = a.MemberId) as LatestSavingsDate,  (Select max(DateCreated) from dbo.[MemberClicks] Where MemberId = a.MemberId) as LastClickDate, a.ClientId as ClientId FROM Member a WHERE a.MemberId > $lastMemberId Order by MemberId asc;";
            $dbresults = DB::connection('sqlsrv')->select($sql);
        }
    }

    private function setMemberAggregate($obj){
        echo "\n processing member ".$obj->MemberId;
        $ma = MemberAggregates::updateOrCreate(
                ['MemberId' => $obj->MemberId],
                ['LatestTr5Date' => Carbon::parse($obj->LatestTr5Date)->format('Y-m-d'),
                    'LatestSavingsDate' => Carbon::parse($obj->LatestSavingsDate)->format('Y-m-d'),
                    'LastClickDate' => Carbon::parse($obj->LastClickDate)->format('Y-m-d'),
                    'ClientId' => $obj->ClientId
                    ]
            );
    }


}