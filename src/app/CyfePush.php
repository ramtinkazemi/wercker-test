<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 15/02/2018
 * Time: 17:01
 */

namespace App;


/**
 *
 * utility class to send data to cyfe
 *
 * Class CyfePush
 * @package App
 */
class CyfePush
{
    protected $headers, $arr, $endpoint, $valueWhenKeyMissing, $settingsArr, $responseStatus, $cyfeParams, $logDescription, $valueInErrorReponse;

    public function __construct($headers, $arr, $endpoint, $valueWhenKeyMissing, $responseStatus, $logDescription, $valueInErrorReponse, $settingsArr)
    {
        $this->headers = $headers;
        $this->arr = $arr;
        $this->endpoint = $endpoint;
        $this->valueWhenKeyMissing = $valueWhenKeyMissing;
        $this->settingsArr = $settingsArr;
        $this->responseStatus = $responseStatus;
        $this->logDescription;
        $this->valueInErrorReponse = $valueInErrorReponse;

        $this->getCyfe();
        $this->setCyfe();
    }

    /**
     * prepare the cyfe content
     */
    private function getCyfe(){
        $this->headers;
        $arr = $this->arr;
        $params['data'] = [];
        $widgetHeaders = $this->headers;
        $data = ['Date' => date('Ymd')];
        foreach($this->headers as $header){
            if($this->responseStatus == true) {
                if(array_key_exists($header, $arr)){
                    $data[$header] = $arr[$header];
                }else{ //array key does not exist in the response data
                    $data[$header] = $this->valueWhenKeyMissing; //typically 0
                }
            }elseif($this->responseStatus == false){ //failed to get content from risk service
                $data[$header] = $this->valueInErrorReponse; //example -1
            }
        }
        $params['data'][] = $data;
        $params['onduplicate'] = cyfeGetArraySettings($widgetHeaders, 1, "replace", "onduplicate");
        $params['cumulative'] = cyfeGetArraySettings($widgetHeaders, 1, "replace", "cumulative");
        $this->cyfeParams = $params;
    }

    /**
     * post to cyfe
     */
    private function setCyfe(){
        if(env('APP_ENV') == "prod") {
            sendToCyfe($this->cyfeParams, $this->endpoint);
            CRLog("debug", $this->logDescription, "", __CLASS__, __FUNCTION__, __LINE__);
        }else{
            CRLog("info", "Sorry will not post to CYFE as ".env('APP_ENV')." is not allowed", $this->logDescription, __CLASS__, __FUNCTION__, __LINE__);
        }

    }

}