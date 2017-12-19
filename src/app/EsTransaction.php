<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 19/12/2017
 * Time: 11:04
 */

namespace App;

use DB;
use App\EsIndex;
use Exception;
use Carbon\Carbon;

class EsTransaction
{

    protected $cacheExpiry;

    public function __construct()
    {

        $this->cacheExpiry = 1440;
    }

    /**
     *
     * Returns only the counts of a particular query and no actual transactions
     *
     * @param $queryString
     * @param $index
     * @param $indexType
     * @return integer
     */
    public function getTotalAggResultsForQuery($queryString, $index, $indexType){
        $esi = new EsIndex();
        $client = $esi->client;
        $json = '{
                  "query": {
                    "bool": {
                      "must": [
                        {
                          "query_string": {
                            "analyze_wildcard": true,
                            "query": "'.$queryString.'"
                          }
                        }
                      ]
                    }
                  },
                  "size": 0
                }';
        $params = [
            'index' => $index,
            'type' => $indexType,
            'body' => $json
        ];
        $hits = $client->search($params);
        return $hits['hits']['total'];
    }


}