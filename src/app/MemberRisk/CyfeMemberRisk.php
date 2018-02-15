<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 13/02/2018
 * Time: 19:51
 */

namespace App\MemberRisk;


/**
 * Class CyfeMemberRisk
 * @package App\MemberRisk
 * @todo unit tests
 */
class CyfeMemberRisk
{

    protected $tableCounts;
    protected $response;
    protected $tables;
    protected $cyfeParams;
    protected $riskTypes;
    protected $responseStatus; //true or false depending on the guzzle request success
    protected $headers;

    public function __construct(){
        $this->tables = ['MemberRisk', 'MemberRisk2FA', 'MemberRiskActivity', 'MemberRiskBlackList', 'MemberRiskSummary', 'MemberRiskWhiteList', 'MemberProfileRefreshQueue'];
        $this->riskTypes = ['Gaming', 'SiteUsage', '2FA', 'PaymentDetails'];
        $this->other = ['IsRisky', 'WhiteList'];
        $this->response =  getRiskService(envDB(['CRUTILS_RISK_SUMMARY', 'http://internal-alb-crutils-metrics-2022242258.ap-southeast-2.elb.amazonaws.com:81/api/1/memberrisksummaryreport']), "GET"); //getRiskService(envDB(['CYFE_RISK_SUMMARY_URL', 'https://app.cyfe.com/api/push/5a84fe713039f3191809084036597']), "GET");
        $this->responseStatus = $this->response['result-success'];
        $this->setTables();
        $this->setRiskTypes();
        //@todo boolean flags in the $this->other array
    }

    /**
     * db table information
     */
    private function setTables(){
        $cyfePush = new \App\CyfePush(
            $this->tables, $this->response['response-body']['tableCounts'],
            envDB(['CYFE_RISK_SUMMARY_TABLES_URL', 'https://app.cyfe.com/api/push/5a85331352c7d5779794304048754']),
            0,
            $this->response['result-success'],
            "CYFE risk summary tables sent",
            -1,
            []
        );
    }

    /**
     * risk profile counts by type
     */
    private function setRiskTypes(){
        $data = $this->getMemberRiskArray($this->response['response-body']['profilesBreakdown']);
        $cyfePush = new \App\CyfePush(
            $this->headers, $data,
            envDB(['CYFE_RISK_SUMMARY_MEMBER_PROFILES_URL', 'https://app.cyfe.com/api/push/5a8532c28ce727207854014048753']),
            0,
            $this->response['result-success'],
            "CYFE risk profiles by types",
            -1,
            []
        );
    }

    /**
     * filter out all the categories not needed for low risk
     */
    private function getMemberRiskArray(){
        $na = [];
        $this->headers = [];
        foreach($this->response['response-body']['profilesBreakdown'] as $riskType=>$arr){
            if(in_array($riskType, $this->riskTypes)){
                $na[$riskType."-high"] = 0;
                if(array_key_exists('high', $arr)){
                    $this->headers[] = $riskType."-high";
                    echo "key exists high for $riskType \n";
                    $na[$riskType."-high"] = $arr['high'];
                }
                if(array_key_exists('medium', $arr)){
                    if($arr['medium'] > 0){
                        $this->headers[] = $riskType."-medium";
                        echo "key exists medium for $riskType \n";
                        $na[$riskType."-medium"] = $arr['medium'];
                    }
                }
            }
        }
        return $na;
    }





}