<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 10/01/2018
 * Time: 11:31
 */

namespace App;
use Aws\Sdk;

class SQSMetric
{
    protected $client;
    protected $queues;
    public $data;
    public $csv;

    public function __construct()
    {
        //initialise
        $this->data = [];
        $this->csv = "";

        //get data
        $this->getClient();
        $this->getAllQueues();
        $this->getSortedResults();
        $this->getCSV();
    }

    public function getClient()
    {
        $sdk = new \Aws\Sdk([
                'version' => 'latest',
                'region' => env('AWS_REGION'),
                'credentials' => [
                    'key' => env('AWS_KEY'),
                    'secret' => env('AWS_SECRET'),
                ]
            ]
        );
        // Get the client from the builder by namespace
        $this->client = $sdk->createSqs();
    }

    /**
     * get a list of all queues
     */
    private function getAllQueues()
    {
        $result = $this->client->listQueues();
        foreach ($result->get('QueueUrls') as $queueUrl) {
            $this->getQueueVisible($queueUrl);
        }
    }

    /**
     *
     * get values of all visible messages
     *
     * @param $queueURL
     */
    private function getQueueVisible($queueURL)
    {
        $result = $this->client->getQueueAttributes(
            [
                'QueueUrl' => $queueURL, // QueueUrl is required
                'AttributeNames' => ['ApproximateNumberOfMessages']
            ]
        );
        $this->data[$queueURL]['visibleMessages'] = $result->get('Attributes')['ApproximateNumberOfMessages'];
        $this->data[$queueURL]['queue'] = substr($queueURL, strrpos($queueURL, '/') + 1);
    }

    /**
     * sort the results by pending messages
     */
    private function getSortedResults()
    {
        //collect is a laravel framework helper which I use for sorting by volume, if not using laravel you can change this to your prefered option or comment out
        $collection = collect($this->data);
        $this->data = $collection->sortByDesc('visibleMessages');
    }

    /**
     * gets the csv string to save the file
     */
    private function getCSV()
    {
        $lines[] = "queue,messages visbile";
        foreach ($this->data as $key => $queue) {
            if($queue['visibleMessages'] > 0){
                $lines[] = $queue['queue'] . "," . $queue['visibleMessages'];
            }
        }
        $this->csv = implode("\n", $lines);
        // laravel storage
        if(env('APP_ENV') != 'testing') {
            \Illuminate\Support\Facades\Storage::disk('s3')->put("cyfe/aws-sqs-queues.csv", $this->csv);
        }
    }
}
