<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 21/09/2017
 * Time: 10:51
 */

namespace App;
use DB;

class CRLibrato
{

    public function __construct()
    {


    }

    public function curl_exec($curl)
    {
        return  curl_exec($curl);
    }

    /**
     *
     * Send a metric value to Librato
     *
     * @param $metricName
     * @param $value
     * @param $tagArray
     * @return bool
     *
     * @todo move the api key to config
     */
    public function sendMetric($metricName, $value, $tagArray){
        $result = true;
        $metricName = $this->getLibratoFriendlyMetric($metricName);
        $url      = env('LIBRATO_URL');
        $username = env('LIBRATO_USERNAME');
        $api_key  = env('LIBRATO_APIKEY');

        $tagArray['environment'] = env('APP_ENV'); //append the environment tag
        $tagArray['app'] = 'CRutils'; //append the environment tag

        $curl = curl_init($url);
        $curl_post_data = array(
            "measurements" => array(
                array("name" => $metricName, "value" => $value, "tags" => $tagArray)
            )
        );

        $headers = array(
            'Content-Type: application/json'
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));

        curl_setopt($curl, CURLOPT_USERPWD, "$username:$api_key");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        ## Show the payload of the POST
        if(env('APP_DEBUG')==true) {
            //print_r($curl_post_data);
            Log::debug(print_r($curl_post_data, true));
        }
        $result = $this->curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlinfo_total_time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
        curl_close($curl);
        if(env('APP_DEBUG')==true) {
            /*
            color_dump ([
                "HTTP Status Code" => $http_status,
                "CURLINFO_TOTAL_TIME" => $curlinfo_total_time,
                "Result" => $result
            ]);
            */
            Log::debug(print_r([
                "HTTP Status Code" => $http_status,
                "CURLINFO_TOTAL_TIME" => $curlinfo_total_time,
                "Result" => $result
            ], true));
            //echo "HTTP Status Code: " . $http_status . " CURLINFO_TOTAL_TIME: ". $curlinfo_total_time;
            //echo $result;
        }
        if($http_status != 202){
            //echo "\n error with posting metric $metricName and value $value";
            Log::debug("error with posting metric $metricName and value $value");
            $result = false;
        }
        return $result;
    }


    /**
     * Send a collection of data points to Librato
     */
    public function sentRegularMeasurements(){
        $this->sendTransactionCounts();
        return true;
    }

    /**
     *
     * Need to make sure that the metric follows librato convention
     *
     * @param $metric
     * @return mixed
     */
    private function getLibratoFriendlyMetric($metric){
        $metric = str_replace(' ', '-', $metric);
        $metric = str_replace('(', '_', $metric);
        $metric = str_replace(')', '_', $metric);
        $metric = str_replace('$', 'AUD', $metric);
        return $metric;
    }

    /**
     * Sends the transaction counts
     */
    public function sendTransactionCounts(){
        $sql = "SELECT count(*) as total from dbo.[Transaction];";
        $results = DB::select($sql);
        $resultarr = json_decode(json_encode($results),true);
        $this->sendMetric('transaction count DB', $resultarr[0]['total'], array('class'=>'Librato'));
        return true;
    }

}