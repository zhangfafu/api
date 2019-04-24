<?php

/**
 * Description of mycard
 *
 * @author user
 */

namespace Xes\Application\Model\Live\LiveCourse;

use Xes\Lib\Config;
use Xes\Lib\Model;

class LiveClass extends Model
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
    public function searchNewLiveClassLists($where, $order, $limit)
    {
        $search = array();
        $sql = "SELECT * FROM `xes_live_classes` $where $order $limit";
        $params = array();
        $result['list'] = $this->newCourseDb->g($sql, $params);
        $result['sql'] = $sql;
        $sql = "SELECT count(`id`) as `total` FROM `xes_live_classes` $where ";
        $total = $this->newCourseDb->g($sql, $params);
        $result['total'] = $total[0]['total'];
        return $result;
    }

    /**
     * 复杂查询
     *
     * @param type $where
     * @param type $order
     * @param type $limit
     */
    public function searchLiveClassLists($where, $order, $limit)
    {
        $search = array();
        $sql = "SELECT * FROM `xes_live_classes` $where $order $limit";
        $params = array();
        $result['list'] = $this->db->g($sql, $params);

        $sql = "SELECT count(`id`) as `total` FROM `xes_live_classes` $where ";
        $total = $this->db->g($sql, $params);
        $result['total'] = $total[0]['total'];
        return $result;
    }

    /**
     * 通过辅导老师id 获取所在的班
     * @param type $selorId 辅导老师id
     */
    public function getClassBySelorId($selorId)
    {
        $sql = <<<XES
			SELECT *
                FROM
                        `xes_live_classes`
                WHERE
                        `counselor_id` = :counselor_id
                AND
                        `status` != 3
                ORDER BY
                        `id` DESC
XES;
        $params = array(
            array('counselor_id', $selorId, \PDO::PARAM_INT)
        );

        return $this->newCourseDb->g($sql, $params);
    }


    /**
     * 
     * @return []
     */
    public function addInfo($data)
    {

        $result = $this->newCourseDb->c('xes_live_classes', $data);

        $id = $this->newCourseDb->lastInsertId();

        return $id;
    }
    
    /**
     * 根据直播组ID，获取辅导班列表
     * @param  int $groupId 直播组ID
     * @return [type]     [description]
     */
    public function getLiveClasses($groupId)
    {
        $sql = <<<XES
            SELECT `id`,`name`,`group_id`, 'group_name',`course_id`,`course_name`,`grade_ids`,`grade_digits`,`subject_ids`,`subject_digits`,
                                    `counselor_id`,`counselor_name`,`counselor_spell`,`start_time`,`end_time`,`creater_id`,`create_time`
            FROM `xes_live_classes`
            WHERE `group_id` = :group_id
            AND `is_del` = 0
            AND `is_close` = 0
            ORDER BY `id` DESC
XES;

        $params = array(
            array('group_id', $groupId, \PDO::PARAM_INT)
        );
        return $this->db->g($sql, $params);
    }
    
        /**
     * 根据直播组ID，获取辅导班列表
     * @param  int $courseId 课程ID
     * @return [type]     [description]
     */
    public function getNewLiveClasses($courseId)
    {
        $sql = <<<XES
            SELECT *
            FROM `xes_live_classes`
            WHERE `course_id` = :course_id
            AND `status` = 1
            ORDER BY `id` DESC
XES;

        $params = array(
            array('course_id', $courseId, \PDO::PARAM_INT)
        );
        return $this->newCourseDb->g($sql, $params);
    }

    /**
     * 根据辅导班ID，获取辅导班信息
     * @param  int $classId 辅导班ID
     * @return [type]     [description]
     */
    public function getLiveClassInfo($classId)
    {
        $sql = <<<XES
            SELECT *
            FROM `xes_live_classes`
            WHERE `id` = :id
XES;

        $params = array(
            array('id', $classId, \PDO::PARAM_INT)
        );
        $result = $this->newCourseDb->g($sql, $params);
        
        if (!empty($result)) {
            $courseId = $result[0]['course_id'];
            $liveClassC = \Xes\Lib\G('\Xes\Application\Component\Live\LiveCourse\LiveClass');
            $result[0]['group_id'] = $liveClassC->getGroupIdByNewCourseId($courseId);
        }

        return $result;
    }

    /**
     *
     * @param int $classId
     * @return int根据条件辅导班ID，获取续报课程班级ID
     * @param int $classId 条件课程班ID
     * @return int $classId 续报班级ID
     */
//    public function getConditionClassId($classId)
//    {
//        $sql = <<<XES
//            SELECT `id`
//            FROM `xes_live_classes`
//            WHERE `condition_class_id` = :id AND `create_type` = 3 AND `is_close` = 0
//XES;
//
//        $params = array(
//            array('id', $classId, \PDO::PARAM_INT)
//        );
//        return $this->db->g($sql, $params);
//    }

    /**
     * 获取所有有续报关系的条件课班级ID和续报班ID
     */
    public function getClassId()
    {
        $sql = <<<XES
            SELECT `id`, `condition_class_id`, `course_id`
            FROM `xes_live_classes`
            WHERE `create_type` = 3 AND `is_close` = 0 AND `condition_class_id` <> 0
XES;

        $params = array();
        return $this->db->g($sql, $params);
    }

    /**
     * 获取所有当天之前结束的未关闭的续报班(create_type=3)
     */
    public function getContinuationClassIds()
    {
        $sql = <<<XES
            SELECT `id`
            FROM `xes_live_classes`
            WHERE `end_time` = date_sub(current_date(),interval 1 day) AND `create_type` = 3 AND `is_close` = 0
XES;

        $params = array();
        return $this->db->g($sql, $params);
    }

    /**
     * 获取所有当天之前结束的未关闭的无辅导老师的班
     */
    public function getNoTutorClassIds()
    {
        $sql = <<<XES
            SELECT `id`
            FROM `xes_live_classes`
            WHERE `end_time` = date_sub(current_date(),interval 1 day) AND `create_type` = 1 AND `is_close` = 0 AND `counselor_id` = 1162
XES;

        $params = array();
        return $this->db->g($sql, $params);
    }

    /**

     * @param int $classId
     * @param type $classType
     * @return \Xes\Application\Model\Live\LiveCourse\[type]  * 根据辅导班ID，更新辅导班班级类型
     * @param  int $classId 辅导班ID
     * @return [type]     [description]
     */
    public function updateClassType($classId, $classType)
    {

        $sql = <<<XES
            UPDATE `xes_live_classes`
            SET `class_type` = '{$classType}'  WHERE `id` = '{$classId}'
XES;

        return $this->db->ud($sql, array());
    }

    /**
     * 根据学员ID和班级ID，获取用户购课ID
     * @param  int $classId 辅导班ID
     * @param  int $stuId 学员ID
     * @return [type]     [description]
     */
    public function getStuCourseIdByClassId($stuId, $classId)
    {

        $query = <<<EE
        SELECT `stu_course_id`
		FROM `xes_live_students` WHERE `stu_id` = :stu_id AND `class_id` = :class_id
EE;
        $params = array(
            array('stu_id', $stuId, \PDO::PARAM_INT),
            array('class_id', $classId, \PDO::PARAM_INT)
        );

        $result = $this->db->g($query, $params);
        if (empty($result)) {
            return 0;
        }
        return $result[0]['stu_course_id'];
    }

    /**
     * 根据用户IDs，班级ID获取原订单号
     * @param $stuIds   用户购课ID array
     * @param $classId  班级ID
     * @return $orderNum
     */
    public function getOrderNum($stuIds, $classId)
    {

        $stuIds = implode(',', $stuIds);
        $sql = <<<XES
            SELECT `stu_id`,`order_num` FROM `xes_live_students`
            WHERE `class_id` = :classId AND `stu_id` IN ($stuIds)
            AND `status` = 1 AND `order_num` <> ''
XES;

        $params = array(
            array('classId', $classId, \PDO::PARAM_INT)
        );

        $result = $this->db->g($sql, $params);
        return $result;
    }

    /**
     * 根据学员ID和组ID，获取用户购课ID
     * @param  int $groupId 组ID
     * @param  int $stuId 学员ID
     * @return [type]     [description]
     */
    public function getStuCourseIdByGroupId($stuId, $group_id)
    {

        $query = <<<EE
        SELECT `stu_course_id`
		FROM `xes_live_students` WHERE `stu_id` = :stu_id AND `group_id` = :group_id
EE;
        $params = array(
            array('stu_id', $stuId, \PDO::PARAM_INT),
            array('group_id', $group_id, \PDO::PARAM_INT)
        );

        $result = $this->db->g($query, $params);
        if (empty($result)) {
            return 0;
        }
        return $result[0]['stu_course_id'];
    }


 //插入独立专注力链接类试题的结果
    public function submitConcentration($data = array())
    {
        $result = false;
        if (!empty($data)){
            $tbl = 'xes_stu_coursewareh5_concentration';
            $result = $this->db->c($tbl, $data);
        }
        return $result;
    }
}
