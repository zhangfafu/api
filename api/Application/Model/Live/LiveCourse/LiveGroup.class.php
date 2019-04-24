<?php

/**
 * Description of mycard
 *
 * @author user
 * 
 */

namespace Xes\Application\Model\Live\LiveCourse;

use Xes\Lib\Config;
use Xes\Lib\Model;

class LiveGroup extends Model
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
     * 复杂查询
     *
     * @param type $where
     * @param type $order
     * @param type $limit
     */
    public function searchLiveGroupLists($where, $order, $limit)
    {
        $search = array();
        $sql = "SELECT * FROM `xes_courses` $where $order $limit";
        $params = array();
        $result['list'] = $this->newCourseDb->g($sql, $params);
        //return $result;
        $result['sql'] = $sql;
        $sql = "SELECT count(`id`) as `total` FROM `xes_courses` $where ";
        $total = $this->newCourseDb->g($sql, $params);
        $result['total'] = $total[0]['total'];

        return $result;
    }

    /**
     * 根据课程Id寻找排期Id
     * @param $order
     * @return mixed
     */
    public function searchLiveGroupRelationCourse($order)
    {
        $sql = "SELECT * FROM `xes_relation_courses` WHERE `new_course_id` IN $order ORDER BY `new_course_id` DESC";
        $params = array();
        $result= $this->newCourseDb->g($sql,$params);
        return $result;
    }

    /**
     * 根据课程Id即courseId获取直播场次
     * @param $order
     * @return string
     */
    /*
    public function searchPlanIds($order)
    {
        $sql = "SELECT `show_plan_ids` FROM `xes_course_contents` WHERE course_id=$order";
        $params = array();
        $result= $this->newCourseDb->g($sql,$params);
        return $result;
    }
*/
    /**
     * 根据课程Id查找课程信息
     * @param $order
     * @return mixed
     */
    /*
    public function searchCourseInfo($order)
    {
        $sql = "SELECT * FROM `xes_courses` WHERE id=$order";
        $params = array();
        $result= $this->newCourseDb->g($sql,$params);
        return $result;
    }
*/
    /**
     * 根据课程ID，获取直播组列表
     * @param  int $groupId 直播组ID
     * @return [type]     [description]
     */
    public function getLiveGroups($courseId, $isDel = null)
    {
        $sql = <<<XES
            SELECT *
            FROM `xes_live_groups`
            WHERE `course_id` = :course_id
XES;
        if (!is_null($isDel)) {
            $sql .= " AND `is_del` = $isDel";
        }

        $params = array(
            array('course_id', $courseId, \PDO::PARAM_INT)
        );
        return $this->db->g($sql, $params);
    }

    /**
     * 保存直播组状态修改日志
     * @param  $tablename
     * @param  $data 需要保存的数据
     * @return [type]     [description]
     */
    public function saveGroupStateChangeLog($tablename, $data)
    {

        return $this->db->c($tablename, $data);
    }

    /**
     * 根据直播组ID，获取直播组信息
     * @param  int $groupId 直播组ID
     * @return [type]     [description]
     */
    public function getLiveGroupInfo($groupId)
    {
        $sql = <<<XES
            SELECT *
            FROM `xes_live_groups`
            WHERE `id` = :id
XES;

        $params = array(
            array('id', $groupId, \PDO::PARAM_INT)
        );
        return $this->db->g($sql, $params);
    }

    /**
     * 根据在售课程ID，获取所有合法的直播组
     * @param int $courseIds 课程IDS
     * @return [type] [description]
     */
    public function getLiveGroupIds($courseIds = array())
    {
        $date = date('Y-m-d');
        $courseIds = implode(',', $courseIds);
        $sql = <<<XES
            SELECT `id`
            FROM `xes_live_groups`
            WHERE `is_del` = 0 AND `course_id` <> '' AND `end_time` >= '{$date}'
            AND `show_start_time` <= '{$date}' AND `show_end_time` >= '{$date}' AND `course_id` IN ($courseIds)
XES;

        $result = $this->db->g($sql, array());

        return $result;
    }

    /**
     * 根据组ID获取组信息
     *
     */
    public function getLiveGroupInfos($groupIds = array())
    {
        $groupIds = implode(',', $groupIds);

        $sql = <<<XES
            SELECT * FROM `xes_live_groups`
            WHERE `id` IN ($groupIds);
XES;

        return $this->db->g($sql, array());
    }

    /**
     * 更新组班级限额
     */
    public function udGroupClassLimitNum($groupIds)
    {
        $groupIds = implode(',', $groupIds);

        $sql = <<<XES
	        UPDATE `xes_live_groups` SET `class_limit_num` = 300 WHERE `id` IN ({$groupIds});
XES;
        $result = $this->db->ud($sql, array());
        if (empty($result)) {
            return false;
        }
        return true;
    }

    /**
     * 根据课程ID，获取所有未删除标准直播组
     * @param int $courseIds 课程IDS
     * @return [type] [description]
     */
    public function getShowLiveGroupIds($courseIds = array())
    {
        $date = date('Y-m-d');
        $courseIds = implode(',', $courseIds);
        $sql = <<<XES
            SELECT `id`
            FROM `xes_live_groups`
            WHERE is_show = 1
            AND is_del=0
            AND status = 1
            AND `course_id` <> ''
            AND `course_id` IN ($courseIds)
XES;
        $result = $this->db->g($sql, array());
        return $result;
    }

    /**
     * 根据条件获得所有的课程ID
     * @return [type] [description]
     */
    public function searchCourseIds($where, $order, $limit)
    {
        $sql = "SELECT `course_id` FROM `xes_live_groups` $where $order $limit";
        $result = $this->db->g($sql, array());
        return $result;
    }
    /**
     * 新的course库获取课程对应的场次包ids
     * @param array $courseIds
     */
    public function getPackageIdsByCourseIds($courseIds){
        $sql = <<<XES
            SELECT `plan_package_ids`,`course_id`
            FROM `xes_course_contents`
            WHERE `course_id` IN ($courseIds)
XES;
        $result = $this->newCourseDb->g($sql, array());
        return $result;
    }

    public function getCourseIdByPackageId($packageId){
        $sql = <<<XES
            SELECT `course_id`
            FROM `xes_course_contents`
            WHERE `plan_package_ids` like "%$packageId%";
XES;
        $result = $this->newCourseDb->g($sql, array());
        return $result;
    }
}
