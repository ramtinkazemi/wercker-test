<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 06/02/2018
 * Time: 15:16
 */

namespace App\ESTransactionsApprovals;

use DB;

/**
 * Class TransactionsPending , updates the CYFE widgets for pending transactions
 * @package App\ESTransactionsApprovals
 */
class TransactionsPending
{
    public $pendingDB;
    public $pendingES;

    public function __construct($params)
    {
        CRLog("debug", "CYFE updating pending transactions", "", __CLASS__, __FUNCTION__, __LINE__);
        $this->getPendingDB();
        $this->getPendingEs();
        $this->setCyfe();
    }


    /**
     * get the number of pending transactions in ES
     */
    private function getPendingEs()
    {
        $est = new \App\EsTransaction();
        $this->pendingES['total'] = $est->getTotalAggResultsForQuery("+transactionstatusid:100", 'cr-db-transactions-approvals-*', 'cr-db-transactions-approvals');
    }

    /**
     * get the number of pending transactions in DB
     */
    private function getPendingDB()
    {
        $rs = DB::connection('sqlsrv')->select("SELECT count(*) as total From [dbo].[Transaction] Where TransactionStatusId = 100;");
        $this->pendingDB['total'] = $rs[0]->total;
    }

    private function setCyfe()
    {
        $endpont = "https://app.cyfe.com/api/push/5a7930b683ea27115092234018187";
        $widgetHeaders = [
            'Transactions pending DB',
            'Transactions pending ES'
        ];
        $total['Date'] = date('Ymd');
        $total['Transactions pending DB'] = $this->pendingDB['total'];
        $total['Transactions pending ES'] = $this->pendingES['total'];
        $params['data'][] = $total;
        $params['onduplicate'] = $this->getArraySettings($widgetHeaders, 1, "replace", "onduplicate");
        $params['cumulative'] = $this->getArraySettings($widgetHeaders, 1, "replace", "cumulative");
        if(env('APP_ENV') == "prod") {
            sendToCyfe($params, env('CYFE_PENDING_TRANSACTIONS', $endpont));
        }
    }

    private function getArraySettings($widgetHeaders, $cumulative, $replace, $fieldToReturn)
    {
        $params = [];
        foreach ($widgetHeaders as $header) {
            $params['onduplicate'][$header] = $replace;
            $params['cumulative'][$header] = $cumulative;
        }
        return $params[$fieldToReturn];
    }

}