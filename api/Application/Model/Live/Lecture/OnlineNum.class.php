<?php

/**
 * Created by PhpStorm.
 * Auth: Qunxu
 * Date: 2016/9/30
 * Time: 14:17
 */

namespace Xes\Application\Model\Live\Lecture;

use Xes\Lib\Config;
use Xes\Lib\Model;

class OnlineNum extends Model
{
    protected $db = null;

    public function __construct()
    {
        $dbConfig = Config::get('\Xes\Application\Config\Storage\Default\ircdb');

        $this->db = \Xes\Lib\G('\Xes\Lib\Pdodb', $dbConfig);
    }

    /*
     * 通过学生id查询他所在过的班级
     * @param int $stuId 学生id
     */
    public function getOnlineNum($classId=13479,$planId=2,$timestamp=0)
    {
        $sql_query = <<<XES
       SELECT onlinenum,unix_timestamp(create_time) timestr,
       create_time FROM `xes_ircapi_chatpeoplenum` 
       WHERE classId={$classId} AND planId={$planId} AND unix_timestamp(create_time)>$timestamp AND onlinenum>2 LIMIT 30 
XES;
        $onlineNums=$this->db->query($sql_query)->fetchAll();
        $i=0;
        foreach ($onlineNums as $onlineNum){
//            if($i==0){
//                $returnData[]=array("id"=>$onlineNum['timestr'],'onlimeNum'=>$onlineNum['onlinenum']);
//                $before=$onlineNum;
//                $i=1;
//            }
//            else{
//                $rate=($onlineNum['onlinenum']-$before['onlinenum'])/300;
//                for($j=0;$j<300;$j++){
//                    $returnData[]=array("id"=>$before['timestr']+$j,'onlimeNum'=>round($before['onlinenum']+$rate*$j));
//                }
//                $before=$onlineNum;
//            }
            $returnData[]=array("id"=>$onlineNum['timestr'],'onlineNum'=>$onlineNum['onlinenum']);
        }
        //返回去重后的班级id
        return $returnData;
    }

}