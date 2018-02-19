<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 19/02/2018
 * Time: 14:41
 */

namespace App\IndexCrutilsLog;

use Carbon\Carbon;

class CrutilsLog
{
    public function __construct(){
        $this->getAggregationOfErrors();
    }

    private function getAggregationOfErrors(){
        $d = Carbon::now()->format('Y-m-d');
        $query  = "timeStamp:[$d TO *]";
        $json = '{
                      "size": 0,
                      "query": {
                        "bool": {
                          "must": [
                            {
                              "query_string": {
                                "query": "'.$query.'",
                                "analyze_wildcard": true
                              }
                            }
                          ],
                          "must_not": []
                        }
                      },
                      "_source": {
                        "excludes": []
                      },
                      "aggs": {
                            "errorTypes": {
                              "terms": {
                                "field": "level.keyword",
                                "size": 5,
                                "order": {
                                  "_count": "desc"
                                }
                              }
                            }
                          }
                    }';
        $esi = new \App\EsIndex();
        $this->setCyfe($this->getData($esi->getResultsByJson($json,'crutils-log-*', 'crutils-log', '', '')));

    }

    /**
     * @param $result
     * @return array
     */
    private function getData($result){
        $nr = [];
        foreach($result['aggregations']['errorTypes']['buckets'] as $key=>$value){
            $nr[$value['key']] = $value['doc_count'];
        }
        return $nr;
    }

    private function setCyfe($result){
        $headers = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];
        $c = new \App\CyfePush($headers, $result, envDB(['CYFE_CRUTILS_LOG_URL' , 'https://app.cyfe.com/api/push/5a8a50fca04bc2417757474056607']), 0, true, '', '-1', []);
    }

}