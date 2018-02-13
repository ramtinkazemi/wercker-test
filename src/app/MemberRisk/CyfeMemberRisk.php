<?php
/**
 * Created by PhpStorm.
 * User: mavperi
 * Date: 13/02/2018
 * Time: 19:51
 */

namespace App\MemberRisk;


class CyfeMemberRisk
{


    /**
     * Update the member dataset
     *
     */
    public function updateMemberDataSet(){
        $params['data'] = [];
        $widgetHeaders = [
            'New members',
            'New members transacting',
            'Risk gaming medium',
            'Risk gaming High',
            'Risk payment high',
        ];

        //get the metrics
        $rs = DB::connection('sqlsrv')->select("SELECT count(*) as total FROM Member as total WHERE DateJoined >= DATEADD(day, DATEDIFF(day, 0, GETDATE()), 0);");
        $params['data'][] = [
            'Date' => date('Ymd'),
            'New members' => 0+$rs[0]->total,
            'New members transacting' => 0, //@todo
            'Risk gaming medium' => MemberRisk::where('Fcolumn', 'S')->count(), //@todo get these from the microservice
            'Risk gaming High' => MemberRisk::where('Fcolumn', 'T')->count(), //@todo get these from the microservice
            'Risk payment high' => MemberRisk::where('PaymentDetailsRisk', true)->count(), //@todo get these from the microservice
        ];

        $params['onduplicate'] = cyfeGetArraySettings($widgetHeaders, 1, "replace", "onduplicate");
        $params['cumulative'] = cyfeGetArraySettings($widgetHeaders, 1, "replace", "cumulative");
        if(env('APP_ENV') == "prod") {
            sendToCyfe($params, $this->endPointMemberRisk);
        }

        CRLog("debug", "update Member dataSet complete", "", __CLASS__, __FUNCTION__, __LINE__);
    }

}