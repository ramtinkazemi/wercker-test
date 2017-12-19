<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 19/12/2017
 * Time: 11:09
 */

namespace App;
use Elasticsearch\ClientBuilder;
use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use Psr\Http\Message\ResponseInterface;

class EsIndex
{
    protected $client;

    public function __construct()
    {

    }

    public function __get($name)
    {
        if ($name == 'client') {
            return $this->getClient();
        } elseif (property_exists($this, $name)) {
            return $this->{$name};
        }
        return null;
    }

    public function getClient()
    {
        if (!$this->client) {
            $endpoints = [env('ES_HOST')];
            $this->client = $this->createClient($endpoints);
        }
        return $this->client;
    }

    public function createClient($endpoints)
    {
        if( $this->isAwsEndpoint($endpoints))
        {
            $psr7Handler = \Aws\default_http_handler();
            $client = ClientBuilder::create()
                ->setHandler($this->getAwsHandler($psr7Handler))
                ->setHosts($endpoints)
                ->allowBadJSONSerialization()
                ->build();
        }
        else
        {
            $client = ClientBuilder::create()
                ->setHosts($endpoints)
                ->allowBadJSONSerialization()
                ->build();
        }

        return isset($client)? $client : null;
    }

    public function isAwsEndpoint($endpoints)
    {
        return (strstr($endpoints[0],'amazonaws') !== false);
    }

    private function getAwsHandler($psr7Handler)
    {
        $region = "ap-southeast-2";
        $signer = new SignatureV4("es", $region);
        $credentialProvider = CredentialProvider::defaultProvider();
        $handler = function(array $request) use($psr7Handler, $signer, $credentialProvider) {
            // Amazon ES listens on standard ports (443 for HTTPS, 80 for HTTP).
            $request['headers']['host'][0] = parse_url($request['headers']['host'][0], PHP_URL_HOST);
            // Create a PSR-7 request from the array passed to the handler
            $psr7Request = new Request(
                $request['http_method'],
                (new Uri($request['uri']))
                    ->withScheme($request['scheme'])
                    ->withHost($request['headers']['host'][0]),
                $request['headers'],
                $request['body']
            );
            // Sign the PSR-7 request with credentials from the environment
            $signedRequest = $signer->signRequest(
                $psr7Request,
                call_user_func($credentialProvider)->wait()
            );

            // Send the signed request to Amazon ES
            /** @var ResponseInterface $response */
            $response = $psr7Handler($signedRequest)->then(
                function(\Psr\Http\Message\ResponseInterface $r) {
                    return $r;
                }, function($error) {
                return $error['response'];
            }
            )->wait();
            // Convert the PSR-7 response to a RingPHP response
            return new CompletedFutureArray([
                "status" => $response->getStatusCode(),
                "headers" => $response->getHeaders(),
                "body" => $response->getBody()->detach(),
                "transfer_stats" => ["total_time" => 0],
                "effective_url" => (string) $psr7Request->getUri(),
            ]);
        };

        return $handler;
    }




    /**
     *
     * returns a result set from an elasticsearch querystring search
     *
     * @todo untested this is a new method
     *
     * @param $fieldsArr
     * @param $index
     * @param $type
     * @param $queryString
     * @param $scrollid, pass empty string if one not available
     * @param $size
     * @param $scrolltime, for example 30s or 2m - care must be taken on performance for anything higher than 30s
     * @param $analyze_wildcard, for example true
     * @return array
     */
    public function getResultsByQueryStr($fieldsArr, $index, $type, $queryString, $scrollid, $size, $scrolltime, $analyze_wildcard){
        $temp = [];
        foreach($fieldsArr as $key=>$value){
            $temp[$key] = '"'.$value.'"';
        }

        try{
            $client = $this->client;

            $json = '{
                    "_source" : ['.implode(", ", $temp).'],
                    "size" : "'.$size.'",
                    "query": {
                          "query_string": {
                            "query": "'.$queryString.'",
                            "analyze_wildcard": '.$analyze_wildcard.'
                           }
                    }
                }';

            $params = [
                'index' => $index,
                'type' => $type,
                'body' => $json,
                'scroll' => "'.$scrolltime.'"
            ];

            //search or scroll existing result
            if($scrollid != "") {
                $results = $client->scroll(["scroll_id" => $scrollid, "scroll" => $scrolltime]);
            }else{
                $results = $client->search($params);
            }
            unset($client);
            gc_collect_cycles();
            return $results;

        }catch(\Exception $e){
            CRLog("error", "Exception", "queryStirng: $queryString \n".$e->getMessage(), __CLASS__, __FUNCTION__, __LINE__);
        }
    }

    /**
     *
     * returns a result set from an elasticsearch json raw search
     *
     * @param $json
     * @param $index
     * @param $type
     * @param $scrollid
     * @param $scrolltime
     * @return mixed
     */
    public function getResultsByJson($json, $index, $type, $scrollid, $scrolltime){

        try{

            $client = $this->getClient();
            $params = [
                'index' => $index,
                'type' => $type,
                'body' => $json,
                'scroll' => $scrolltime
            ];

            //search or scroll existing result
            if($scrollid != "") {
                $results = $client->scroll(["scroll_id" => $scrollid, "scroll" => $scrolltime]);
            }else{
                $results = $client->search($params);
            }

            unset($params);
            unset($client);
            //print_r($params);
            gc_collect_cycles();
            return $results;

        }catch(\Exception $e){
            CRLog("error", "Exception", "json: \n$json \n".$e->getMessage(), __CLASS__, __FUNCTION__, __LINE__);
        }
    }
}