<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 13/02/2018
 * Time: 19:51
 */

namespace App\MemberRisk;


class CyfeMemberRisk
{

    protected $tableCounts;
    protected $response;
    protected $tables;
    protected $cyfeParams;
    protected $riskTypes;
    protected $responseStatus; //true or false depending on the guzzle request success

    public function __construct(){
        $this->tables = ['MemberRisk', 'MemberRisk2FA', 'MemberRiskActivity', 'MemberRiskBlackList', 'MemberRiskSummary', 'MemberRiskWhiteList', 'MemberProfileRefreshQueue'];
        $this->riskTypes = ['Gaming', 'SiteUsage', '2FA', 'PaymentDetails', 'IsRisky', 'WhiteList', 'BlackList'];
        $this->response =  getRiskService(envDB(['CRUTILS_RISK_SUMMARY', 'http://internal-alb-crutils-metrics-2022242258.ap-southeast-2.elb.amazonaws.com:81/api/1/memberrisksummaryreport']), "GET"); //getRiskService(envDB(['CYFE_RISK_SUMMARY_URL', 'https://app.cyfe.com/api/push/5a84fe713039f3191809084036597']), "GET");
        $this->responseStatus = $this->response['result-success'];




        //$this->getCyfe($this->tables, $this->response['response-body']['tableCounts']);
        //$this->sendToCyfe($this->getCyfe($this->tables, $this->response['response-body']['tableCounts']), envDB(['CYFE_RISK_SUMMARY_TABLES_URL', 'https://app.cyfe.com/api/push/5a84fe713039f3191809084036597']), "CYFE risk summary tables sent");
    }

    private function setTables(){
        $cyfePush = new \App\CyfePush(
            $this->tables, $this->response['response-body']['tableCounts'],
            envDB(['CYFE_RISK_SUMMARY_TABLES_URL', 'https://app.cyfe.com/api/push/5a84fe713039f3191809084036597']),
            0,
            $this->response['result-success'],
            "CYFE risk summary tables sent",
            -1,
            []
        );
    }



    /**
     * filter out all the categories not needed for low risk
     */
    private function prepareMemberRiskArray(){
        foreach($this->response['response-body']['profilesBreakdown'] as $key=>$value){

        }
    }



}