<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 07/02/2018
 * Time: 17:00
 */

namespace App\MemberRisk;

use Carbon\Carbon;
use DB;

/**
 *
 * provides the table counts for the profiles that are processed async
 *
 * Class CyfeMemberRiskStatus
 * @package App\MemberRisk
 */
class CyfeMemberRiskStatus
{
    public $dbResult;

    /**
     * CyfeMemberRiskStatus constructor.
     * @param $arrayParams
     */
    public function __construct($arrayParams)
    {
        $this->getPendingToProcess();
    }


    private function getPendingToProcess(){
        $sql = "select count(*) as total, (SELECT created_at FROM MemberProfileRefreshQueue order by created_at asc LIMIT 1) as created_at FROM MemberProfileRefreshQueue;";
        $this->dbResult =  DB::connection('crutils-risk')->select($sql);
        //print_r($this->dbResult);die;
        $this->setCyfe();
    }


    private function setCyfe()
    {
        $end = Carbon::parse($this->dbResult[0]->created_at);
        $now = Carbon::now();
        $length = $end->diffInHours($now);

        $endpont = "https://app.cyfe.com/api/push/5a7aa38026d495467493114022554"; //@todo move to db
        $widgetHeaders = [
            'profiles to be processed',
            'oldest profile minutes',
            'oldest profile hours'
        ];
        $total['Date'] = date('Ymd');
        $total['profiles to be processed'] = $this->dbResult[0]->total;
        $total['oldest profile minutes'] = $end->diffInMinutes($now);
        $total['oldest profile hours'] = $end->diffInHours($now);;
        $params['data'][] = $total;
        $params['onduplicate'] = cyfeGetArraySettings($widgetHeaders, 1, "replace", "onduplicate");
        $params['cumulative'] = cyfeGetArraySettings($widgetHeaders, 1, "replace", "cumulative");
        print_r($params);
        if(env('APP_ENV') == "prod") {
            sendToCyfe($params, env('CYFE_'.(join('', array_slice(explode('\\', __CLASS__), -1))), $endpont));
        }
    }
}