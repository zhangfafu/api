<?php

/**
 * Description of mycard
 *
 * @author user
 */

namespace Xes\Application\Model\Live\LiveCourse;

use Xes\Lib\Config;
use Xes\Lib\Model;

class LivePlan extends Model
{

    protected $db = null;

    public function __construct()
    {
        $dbConfig = Config::get('\Xes\Application\Config\Storage\Default\live');
        $this->db = \Xes\Lib\G('\Xes\Lib\Pdodb', $dbConfig);
        $newCourseDbConfig = Config::get('\Xes\Application\Config\Storage\Default\newcourse');
        $this->newCourseDb = \Xes\Lib\G('\Xes\Lib\Pdodb', $newCourseDbConfig);
    }

    /**
     * 获取指定时间范围指定groupid有哪些planid和groupid对应
     * 复杂查 询
     * auth：qunxu
     */
    public function getPlanToGroup($sday, $eday)
    {
        $sql = "SELECT id,group_id,`name`,`day`,start_time,stime,end_time,etime FROM `xes_live_plans` WHERE `day` >= '{$sday}' AND  day<='$eday'";
        return $this->db->g($sql, array());
    }

    public function getStuNum($planStr)
    {
        //获取买课人数
        $sql = "SELECT GROUP_CONCAT(stu_ids) stu,plan_id FROM `xes_live_plan_class_stuids` where plan_id in ({$planStr}) GROUP BY plan_id";
        return $this->db->g($sql, array());
    }

    /**
     * 获取指定直播评价数据
     * auth：qunxu
     * $planStr 以逗号分隔的直播id串
     * $limit 条数
     */
    public function getEvalInfo($planStr, $limit)
    {
        $sql = "SELECT COUNT(distinct stu_id) evalnum,plan_id,AVG(score1) avgscore1,AVG(score2) avgscore2,AVG(score3) avgscore3,GROUP_CONCAT(score1) score1str,GROUP_CONCAT(score2) score2str,GROUP_CONCAT(score3) score3str FROM `xes_live_plan_stu_evaluates` WHERE `plan_id` in({$planStr}) GROUP BY plan_id ORDER BY create_time desc {$limit}";
        $result['list'] = $this->db->g($sql, array());
        $sql = "SELECT COUNT(distinct plan_id) total FROM `xes_live_plan_stu_evaluates` WHERE `plan_id` in({$planStr})";
        $total = $this->db->g($sql, array());
        $result['total'] = $total[0]['total'];
        return $result;
    }

    /**
     * 根据筛选条件获取学生的评价数据
     * auth：qunxu
     * $planStr 以逗号分隔的直播id串
     * $limit 条数
     */
    public function getStuEvalInfo($where, $limit, $planId)
    {
        $sql = "SELECT * FROM `xes_live_plan_stu_evaluates` {$where} ORDER BY create_time desc {$limit}";
        $result['list'] = $this->db->g($sql, array());
        $sql = "SELECT COUNT(1) filter_num FROM `xes_live_plan_stu_evaluates` {$where}";
        $filter_num = $this->db->g($sql, array());
        $result['filter_num'] = $filter_num[0]['filter_num'];
        $sql = "SELECT COUNT(1) total FROM `xes_live_plan_stu_evaluates` where plan_id='{$planId}'";
        $total = $this->db->g($sql, array());
        $result['total'] = $total[0]['total'];
        return $result;
    }

    /**
     * 获取指定直播有多少人看过
     * auto：qunxu
     */
    public function getWacthNum($planStr)
    {
        $sql = "select plan_id,COUNT(DISTINCT stu_id) watchnum from xes_live_student_online_times where plan_id in ({$planStr}) group by plan_id";
        return $this->db->g($sql, array());
    }

    /**
     * 复杂查询
     *
     * @param type $where
     * @param type $order
     * @param type $limit
     */
    public function searchLivePlanLists($where, $order, $limit)
    {
        $search = array();
        $sql = "SELECT * FROM `xes_live_plans` $where $order $limit";
        $params = array();
        $result['list'] = $this->newCourseDb->g($sql, $params);
        $result['sql'] = $sql;
        $sql = "SELECT count(`id`) as `total` FROM `xes_live_plans` $where ";
        $total = $this->newCourseDb->g($sql, $params);
        $result['total'] = $total[0]['total'];
        return $result;
    }

    /**
     * 根据直播组ID，获取直播计划列表
     * @param  int $groupId 直播组ID
     * @return [type]     [description]
     */
    public function getLivePlans($packageId, $category = 1, $type = null)
    {
        $packageId = empty($packageId) ? 0 : $packageId;
        $sql = <<<XES
            SELECT *
            FROM `xes_live_plans`
            WHERE `package_id` in({$packageId})
XES;
        if (!is_null($type)) {
            $sql .= " AND `type` = $type";
        }

        $sql .= " ORDER BY `stime` asc";

        $params = array();
        return $this->newCourseDb->g($sql, $params);
    }

      /**
     * 根据直播组ID，获取直播计划列表
     * @param  int $groupId 直播组ID
     * @return [type]     [description]
     */
    public function getOldLivePlans($groupId, $category = 1, $isShow = null)
    {
        $groupId = empty($groupId) ? 0 : $groupId;
        $sql = <<<XES
            SELECT *
            FROM `xes_live_plans`
            WHERE `group_id` in({$groupId})
            AND `category` = :category
XES;
        if (!is_null($isShow)) {
            $sql .= " AND `is_show` = $isShow";
        }

        $sql .= " ORDER BY `stime` asc";

        $params = array(
            array('category', $category, \PDO::PARAM_INT)
        );
        return $this->db->g($sql, $params);
    }


    /**
     * 根据直播计划ID，获取直播计划信息
     * @param  int $planId 直播计划ID
     * @return [type]     [description]
     */
    public function getLivePlanInfo($planId)
    {
        $sql = <<<XES
            SELECT *
            FROM `xes_live_plans`
            WHERE `id` = :id
XES;

        $params = array(
            array('id', $planId, \PDO::PARAM_INT)
        );
        return $this->newCourseDb->g($sql, $params);
    }

    /**
     * 查询指定时间内的直播课id
     * @param string $start 起始时间
     * @param string $end 终止时间
     */
    public function getExpireLivePlanId($start, $end)
    {

        $sql = <<<Xes
            SELECT `id` FROM `xes_live_plans` WHERE `day`<='{$end}' AND `day`>='{$start}'
Xes;

        return $this->db->g($sql, array());
    }

    /**
     * 星级复杂查询
     *
     * @param type $where
     * @param type $order
     * @param type $limit
     */
    public function searchLiveKeyWordsLists($where, $order, $limit)
    {
        $search = array();
        $sql = "SELECT * FROM `xes_live_plan_keywords` $where $order $limit";
        $params = array();
        $result['list'] = $this->db->g($sql, $params);
        $result['sql'] = $sql;
        $sql = "SELECT count(1) as `total` FROM `xes_live_plan_keywords` $where ";
        $total = $this->db->g($sql, $params);
        $result['total'] = $total[0]['total'];
        return $result;
    }

    /**
     * 根据关键词ID，获取关键词信息
     * @param  int $id 关键词ID
     */
    public function getKeyWordsInfo($id)
    {
        $sql = <<<XES
            SELECT *
            FROM `xes_live_plan_keywords`
            WHERE `id` = :id
XES;

        $params = array(
            array('id', $id, \PDO::PARAM_INT)
        );
        return $this->db->g($sql, $params);
    }

    /**
     * 星级复杂查询
     *
     * @param type $where
     * @param type $order
     * @param type $limit
     */
    public function searchPlanEvaluateLists($where, $order, $limit)
    {
        $search = array();
        $sql = "SELECT * FROM `xes_live_plan_evaluates` $where $order $limit";
        $params = array();
        $result['list'] = $this->db->g($sql, $params);
        $result['sql'] = $sql;
        $sql = "SELECT count(1) as `total` FROM `xes_live_plan_evaluates` $where ";
        $total = $this->db->g($sql, $params);
        $result['total'] = $total[0]['total'];
        //好评数
        $sql = "SELECT count(1) as `praise` FROM `xes_live_plan_evaluates` $where AND score > 2";
        $praise = $this->db->g($sql, $params);
        $result['praise'] = $praise[0]['praise'];
        return $result;
    }

    /**
     * 用户预约直播体验   日志表
     */
    public function addLivePlanFollow($field)
    {
        $tbl = 'xes_live_plan_follow_logs';
        $result = $this->db->c($tbl, $field);
        return $result;
    }

    /**
     * 获取30分钟内将要直播的直播体验ID
     * where  `stime`> {$time}
     */
    public function getLivePlanId()
    {
        $before = time() + 30 * 60;
        $after = time();
        $sql = <<<XES
            select  `id`,`name` ,`stime`,`group_id` from xes_live_plans where `stime`>{$after} and `stime`<={$before}
XES;
        $res = $this->db->g($sql, array());
        return $res;
    }

    /**
     * 分组查询直播列表
     *
     * @param type $where
     * @param type $order
     * @param type $groupBy
     * @param type $limit
     */
    public function searchLivePlanListsByGroup($where, $order, $groupBy, $limit)
    {

        $search = array();
        $sql = "SELECT * FROM (SELECT * FROM `xes_live_plans` $where $order ) list $groupBy $limit";
        $params = array();
        $result['list'] = $this->db->g($sql, $params);
        $result['total'] = count($result['list']);
        return $result;
    }

    /**
     * 根据日期获取当天的标准直播场次
     * @param type $date
     */
    public function getShowLivePlanByDate($date)
    {

        $sql = <<<XES
            SELECT * FROM `xes_live_plans`
            WHERE `day` = :date AND `is_del` = 0 AND `is_show` = 1
XES;

        $params = array(
            array('date', $date, \PDO::PARAM_STR)
        );

        return $this->db->g($sql, $params);
    }

    /**
     * 根据课程大纲ID获取尚未开始的场次信息
     *
     */
    public function getLivePlanByOutlineIds($outlineIds = array(), $date = '')
    {

        $outlineIds = implode(',', $outlineIds);
        $date = !empty($date) ? $date : date("Y-m-d");

        $sql = <<<XES
            SELECT * FROM `xes_live_plans`
            WHERE `outline_id` IN ($outlineIds) AND `day` >'{$date}' AND `is_del` = 0
XES;

        return $this->db->g($sql, array());
    }

    /**
     * 根据直播计划ids获取具体直播计划信息
     * @param string $planIds 直播计划ids
     * @return array
     */
    public function getPlanInfoByPlanIds($planIds)
    {
        $sql = <<<XES
            SELECT * FROM `xes_live_plans`
            WHERE `id` in ({$planIds})
XES;

        $params = array();
        $result = $this->newCourseDb->g($sql, $params);
        return $result;
    }

    /**
     * 查询数据，获取一个PDOStatement对象，一条一条数据获取
     *
     * @param type $where
     * @param type $order
     * @param type $limit
     * @return type
     */
    public function getLivePlanInfoByDay($day)
    {
        $sql = <<<XES
            SELECT * FROM `xes_live_plans` WHERE day <= '{$day}' AND day >= '{$day}' AND is_del = 0 GROUP BY group_id
XES;
        $liveM = $this->db->getPDOStatement($sql, array());
        return $liveM;
    }

    /**
     * 获取直播排期下某一大纲对应场次信息
     */
    public function getPlanByOutLineId($groupId, $outlineId, $sTime)
    {
        $groupId = implode(',', $groupId);
        $sql = <<<XES
          SELECT * FROM xes_live_plans where group_id in ($groupId)
           AND  outline_id = :outlineId
           AND stime > :stime
           AND is_show = 1
           AND is_del=0
           AND status = 1
XES;

        $params = array(
            array('outlineId', $outlineId, \ PDO::PARAM_INT),
            array('stime', $sTime, \ PDO::PARAM_STR),
        );
        $result = $this->db->g($sql, $params);
        return $result;
    }

    /**
     * @param array $data
     * 添加计算大赛采集的用户图片的url地址
     * @return bool
     */
    public function addComputUrl($data = array())
    {
        $result = false;
        if (!empty($data)){
            $tbl = 'xes_live_compute_url';
            $result = $this->db->c($tbl, $data);
        }
        return $result;
    }
}
