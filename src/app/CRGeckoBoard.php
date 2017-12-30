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
     *
     * @deprecated
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
        CRLog("debug", "post to geckoboard", json_encode($postData, true), __CLASS__, __FUNCTION__, __LINE__);

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
        $this->updateTransactionTotals();
    }

    private function getArraySettings($widgetHeaders, $cumulative, $replace, $fieldToReturn){
        $params = [];
        foreach($widgetHeaders as $header){
            $params['onduplicate'][$header] = $replace;
            $params['cumulative'][$header] = $cumulative;
        }
        return $params[$fieldToReturn];
    }

    /**
     * Update the member dataset
     *
     */
    public function updateMemberDataSet(){
        $params['data'] = [];
        $widgetHeaders = [
            'New members',
            'New members transacting',
            'Risk gaming medium',
            'Risk gaming High',
            'Risk payment high',
        ];

        //get the metrics
        $rs = DB::connection('sqlsrv')->select("SELECT count(*) as total FROM Member as total WHERE DateJoined >= DATEADD(day, DATEDIFF(day, 0, GETDATE()), 0);");
        $params['data'][] = [
            'Date' => date('Ymd'),
            'New members' => 0+$rs[0]->total,
            'New members transacting' => 0, //@todo
            'Risk gaming medium' => MemberRisk::where('Fcolumn', 'S')->count(),
            'Risk gaming High' => MemberRisk::where('Fcolumn', 'T')->count(),
            'Risk payment high' => MemberRisk::where('PaymentDetailsRisk', true)->count(),
        ];

        $params['onduplicate'] = $this->getArraySettings($widgetHeaders, 1, "replace", "onduplicate");
        $params['cumulative'] = $this->getArraySettings($widgetHeaders, 1, "replace", "cumulative");

        sendToCyfe($params, "https://app.cyfe.com/api/push/5a46105ae0e663525741213915974");

        CRLog("debug", "update Member dataSet complete", "", __CLASS__, __FUNCTION__, __LINE__);
    }

    /**
     * Update the system data set
     */
    public function updateSystemDataSet(){
        $params['data'] = [];
        $tAtt['Date'] = date('Ymd');
        $widgetHeaders = [
            'transactioncreated347',
            'transactioncreated1',
            'transactioncreated2',
            'transactioncreated5',
            'transactioncreated6',
            'transactioncreated8',
        ];

        foreach($widgetHeaders as $header){
            $tAtt[$header] = 0;
        }

        // get Transaction breakdown @todo move to the transaction dataset
        $sqlTr = "SELECT count(*) as total, b.DescriptionShort, b.TransactionTypeId FROM [dbo].[Transaction] a, [dbo].[TransactionType] b WHERE a.TransactionTypeId = b.TransactionTypeId AND a.DateCreated >= DATEADD(day, DATEDIFF(day, 0, GETDATE()), 0) GROUP BY b.DescriptionShort, b.TransactionTypeId;";
        $rsArr = DB::connection('sqlsrv')->select($sqlTr);
        foreach($rsArr as $key=>$rs){
            $promoTrs = [3,4,7];
            if(in_array($rs->TransactionTypeId, $promoTrs)){
                $tAtt["transactioncreated347"] = $rs->total + $tAtt["transactioncreated347"];
            }else{
                $tAtt["transactioncreated".$rs->TransactionTypeId] = 0+$rs->total;
            }

        }
        // send to cyfe transactions
        $params['data'][] = $tAtt;
        $params['onduplicate'] = $this->getArraySettings($widgetHeaders, 1, "replace", "onduplicate");
        $params['cumulative'] = $this->getArraySettings($widgetHeaders, 1, "replace", "cumulative");
        sendToCyfe($params, "https://app.cyfe.com/api/push/5a46239ca54e40217215493916031");
        CRLog("debug", "update system dataSet complete", "", __CLASS__, __FUNCTION__, __LINE__);
    }

    public function updateTransactionTotals(){
        $params['data'] = [];
        $widgetHeaders = [
            'DB transactions',
            'ELK transactions',
        ];

        //send to cyfe transaction totals
        // SQL for total transcations
        $rs = DB::connection('sqlsrv')->select("SELECT count(*) as total FRom [dbo].[Transaction];");
        $total['Date'] = date('Ymd');
        $total['DB transactions'] =  0+$rs[0]->total;
        $total['ELK transactions'] = $this->est->getTotalAggResultsForQuery('*', 'cr-db-transactions-approvals*', 'cr-db-transactions-approvals');
        $params['data'][] = $total;
        $params['onduplicate'] = $this->getArraySettings($widgetHeaders, 1, "replace", "onduplicate");
        $params['cumulative'] = $this->getArraySettings($widgetHeaders, 1, "replace", "cumulative");

        sendToCyfe($params, "https://app.cyfe.com/api/push/5a4622bee0d226316695233916029");
        CRLog("debug", "update transactions totals complete", "", __CLASS__, __FUNCTION__, __LINE__);

    }

    /**
     * update the report subscription metrics
     */
    public function updateReportSubscription(){
        $params['data'] = [];
        $widgetHeaders = [
            'DB report subscription',
            'ELK report subscription',
        ];

        $timestamp =  Carbon::now()->addDays(0)->format('Y-m-d');
        $total['Date'] = date('Ymd');
        $total['ELK report subscription'] =  $this->est->getTotalAggResultsForQuery("+LastModifiedDate:[$timestamp TO $timestamp]", env('ES_REPORT_SUB_INDEX')."*", env('ES_REPORT_SUB_TYPE'));
        $rs = DB::connection('sqlsrv')->select("SELECT count(*) as total FRom ReportSubscription;");
        $total['DB report subscription'] = 0+$rs[0]->total;

        $params['data'][] = $total;
        $params['onduplicate'] = $this->getArraySettings($widgetHeaders, 1, "replace", "onduplicate");
        $params['cumulative'] = $this->getArraySettings($widgetHeaders, 1, "replace", "cumulative");

        sendToCyfe($params, "https://app.cyfe.com/api/push/5a46244f550fc2293781123916033");
    }

}