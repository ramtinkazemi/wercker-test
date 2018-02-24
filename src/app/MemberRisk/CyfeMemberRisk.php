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
        $this->response =  getRiskService(envDB(['CRUTILS_RISK_SUMMARY', '']), "GET");
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
            envDB(['CYFE_RISK_SUMMARY_TABLES_URL', '']),
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
            envDB(['CYFE_RISK_SUMMARY_MEMBER_PROFILES_URL', '']),
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
                    $na[$riskType."-high"] = $arr['high'];
                }
                if(array_key_exists('medium', $arr)){
                    if($arr['medium'] > 0){
                        $this->headers[] = $riskType."-medium";
                        $na[$riskType."-medium"] = $arr['medium'];
                    }
                }
            }
        }
        return $na;
    }





}