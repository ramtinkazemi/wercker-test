<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 21/09/2017
 * Time: 10:51 
 */

namespace App;

class CRLibrato
{

    public $result;

    public function __construct($metricName, $value, $app, $taskname, $tagArray)
    {
       // echo "here ";die;
        $this->result = $this->sendMetric($metricName, $value, $app, $taskname, $tagArray);
        //return $this->result;
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
     * @return array
     *
     * @todo move the api key to config
     */
    public function sendMetric($metricName, $value, $app, $taskname, $tagArray){
        $result = [];
        $result['result'] = true;
        $result['httpResponse'] = 200;
        $result['message'] = 'OK';
        $result['messageDetailed'] = '';
       // print_r($result);die;

        $metricName = $this->getLibratoFriendlyMetric($metricName);
        $url      = env('LIBRATO_URL');
        $username = env('LIBRATO_USERNAME');
        $api_key  = env('LIBRATO_APIKEY');

        $tagArray['environment'] = env('APP_ENV'); //append the environment tag
        $tagArray['app'] = $app; //append the app tag
        $tagArray['taskname'] = $taskname; //append the app task name tag

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
        CRLog("debug", "curl post", json_encode($curl_post_data), __CLASS__, __FUNCTION__, __LINE__);

        // get post details
        $result['messageDetailed'] = $this->curl_exec($curl).", time taken  ".curl_getinfo($curl, CURLINFO_TOTAL_TIME);
        $result['httpResponse'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        CRLog("debug", "Librato send", json_encode([
                "HTTP Status Code" => $result['httpResponse'],
                "Result" => json_encode($result)
            ]), __CLASS__, __FUNCTION__, __LINE__);

        if($result['httpResponse'] != 202){
            $result['result'] = false;
            $result['message'] = "error with posting metric $metricName and value $value to Librato. ".$result['messageDetailed'];
        }
       // print_r($result);die;
        return $result;
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


}