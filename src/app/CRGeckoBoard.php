<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 19/12/2017
 * Time: 10:55
 */

namespace App;

use GuzzleHttp;
use Carbon\Carbon;
use DB;
use App\MemberRisk;
use App\EsTransaction;


class CRGeckoBoard
{
    public $est; //EsTransaction instance

    public function __construct()
    {
        $this->est = new EsTransaction();
    }

    /**
     * @param $dataSet
     * @param $metricArr, an array of metrics
     * @param $timestamp, if not set defaults to current
     *
     * @todo add counts of transactions from db and elk so we can compare
     * @todo add counts of report subscription checks in the last 15 minutes
     * @todo update last seven days
     */
    public function send($dataSet, $metricArr, $timestamp){
        if($dataSet == ""){ //set default dataset if not set
            $dataSet = "system";
        }
        if($timestamp == ""){ //set timestamp to now if not set
            $timestamp =  Carbon::now()->format('Y-m-d'); //Carbon::now()->format('Y-m-d') ; // $carbon = new Carbon()->format('Y-m-d H'))->toda;

        }
        $metricArr['timestamp'] = $timestamp;

        // prepare the post data
        $postData['data'][] = $metricArr;
        CRLog("debug", "post to geckoboard", print_r($postData), __CLASS__, __FUNCTION__, __LINE__);

        //send
        $postData = json_encode($postData, JSON_OBJECT_AS_ARRAY);
        $client = new GuzzleHttp\Client();

        $uri = 'https://api.geckoboard.com/datasets/'.$dataSet.'/data';

        if(env('GECKOBOARD_POST', false) == true){
            $response = $client->post($uri, [
                'headers'         => [
                    'Authorization' => 'Basic '.env('GECKOBOARD_API_KEY'),
                ],

                'body'            => $postData,
                'allow_redirects' => false,
                'timeout'         => 30
            ]);
            CRLog("debug", "post to geckoboard done sending", "response : ".$response->getStatusCode(),__CLASS__, __FUNCTION__, __LINE__);

        }else{
            CRLog("debug", "Not sending payload to geckoboard", "", __CLASS__, __FUNCTION__, __LINE__);
        }
        return true;
    }

    /**
     * Send the various batches
     */
    public function sendBatchSQL(){
        $this->updateMemberDataSet();
        $this->updateSystemDataSet();
        $this->updateReportSubscription();
    }

    /**
     * Update the member dataset
     *
     */
    public function updateMemberDataSet(){
        //get the metrics with zero so that the widgets are always populated
        $tAtt['newmembers'] = 0;
        $tAtt['newtransacting'] = 0; //@todo
        $tAtt['riskpaymenttrue'] = MemberRisk::where('PaymentDetailsRisk', true)->count();
        $tAtt['riskpromotrue'] = MemberRisk::where('Fcolumn', 'T')->count();
        $tAtt['riskpromosoft'] = MemberRisk::where('Fcolumn', 'S')->count();
        $tAtt['riskpromotandpaymenttrue'] = MemberRisk::where('Fcolumn', 'T')->where('PaymentDetailsRisk', true)->count();

        $sqlArr[] = ["sql" => "SELECT count(*) as total FROM Member as total WHERE DateJoined >= DATEADD(day, DATEDIFF(day, 0, GETDATE()), 0);", "metricName" => "newmembers"];
        foreach($sqlArr as $key=>$value){
            $rs = DB::select($value['sql']);
            $tAtt[$value['metricName']] = 0+$rs[0]->total;
        }
        $this->send('member', $tAtt, '');
        CRLog("debug", "update Member dataSet complete", "", __CLASS__, __FUNCTION__, __LINE__);
    }

    /**
     * Update the system data set
     */
    public function updateSystemDataSet(){
        //get the metrics with zero so that the widgets are always populated
        $tAtt['transactioncreated347'] = 0;
        $tAtt['transactioncreated1'] = 0;
        $tAtt['transactioncreated2'] = 0;
        $tAtt['transactioncreated5'] = 0;
        $tAtt['transactioncreated6'] = 0;
        $tAtt['transactioncreated8'] = 0;


        //get total elk transactions
        $tAtt['totaltransactionselk'] = $this->est->getTotalAggResultsForQuery('*', 'cr-db-transactions-approvals', 'cr-db-transactions-approvals');

        // SQL for report subscription
        $sqlArr[] = ["sql" => "SELECT count(*) as total FRom [dbo].[Transaction];", "metricName" => "totaltransactions"];
        foreach($sqlArr as $key=>$value){
            $rs = DB::select($value['sql']);
            $tAtt[$value['metricName']] = 0+$rs[0]->total;
        }

        // get Transaction breakdown @todo move to the transaction dataset
        $sqlTr = "SELECT count(*) as total, b.DescriptionShort, b.TransactionTypeId FROM [dbo].[Transaction] a, [dbo].[TransactionType] b WHERE a.TransactionTypeId = b.TransactionTypeId AND a.DateCreated >= DATEADD(day, DATEDIFF(day, 0, GETDATE()), 0) GROUP BY b.DescriptionShort, b.TransactionTypeId;";
        $rsArr = DB::select($sqlTr);
        foreach($rsArr as $key=>$rs){
            $promoTrs = [3,4,7];
            if(in_array($rs->TransactionTypeId, $promoTrs)){
                $tAtt["transactioncreated347"] = $rs->total + $tAtt["transactioncreated347"];
            }else{
                $tAtt["transactioncreated".$rs->TransactionTypeId] = 0+$rs->total;
            }

        }
        $this->send('', $tAtt, '');
        CRLog("debug", "update system dataSet complete", "", __CLASS__, __FUNCTION__, __LINE__);
    }

    /**
     * update the report subscription metrics
     */
    public function updateReportSubscription(){
        $timestamp =  Carbon::now()->addDays(0)->format('Y-m-d');

        $sqlArr[] = ["sql" => "SELECT count(*) as total FRom ReportSubscription;", "metricName" => "reportsubscriptionrows"];
        foreach($sqlArr as $key=>$value){
            $rs = DB::select($value['sql']);
            $tAtt[$value['metricName']] = 0+$rs[0]->total;
        }
        $tAtt['reportsubscriptionelkcount'] = $this->est->getTotalAggResultsForQuery("+LastModifiedDate:[$timestamp TO $timestamp]", env('ES_REPORT_SUB_INDEX'), env('ES_REPORT_SUB_TYPE'));

        $this->send("reportsubscription", $tAtt,  $timestamp);
    }

}