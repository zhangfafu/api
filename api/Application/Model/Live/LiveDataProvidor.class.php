<?php

/**
 * Created by PhpStorm.
 * Auth: Qunxu
 * Date: 2016/9/30
 * Time: 14:17
 */

namespace Xes\Application\Model\Live;

use Xes\Lib\Config;
use Xes\Lib\Model;

class LiveDataProvidor extends Model
{
    protected $db = null;

    public function __construct()
    {
        $dbConfig = Config::get('\Xes\Application\Config\Storage\Default\live');
        $this->db = \Xes\Lib\G('\Xes\Lib\Pdodb', $dbConfig);
    }

    /*
     * 通过学生id查询他所在过的班级
     * @param int $stuId 学生id
     */
    public function getStuOnceInClassLists($stuId)
    {
        $sql_query = <<<xrs
        SELECT GROUP_CONCAT(class_id) class_id FROM `xes_live_students` WHERE stu_id={$stuId}
        UNION
        SELECT GROUP_CONCAT(concat(class_id_from,',',class_id_to)) class_id FROM `xes_live_student_logs` WHERE stu_id={$stuId}
xrs;
        $classIdstrs=$this->db->query($sql_query)->fetchAll();
        $classIdstr=implode(',',$classIdstrs);
        return explode(',',$classIdstr);
    }

    public function getStuListBeInClass($classId)
    {
        $sql_query = "SELECT GROUP_CONCAT(DISTINCT stu_ids) stu_ids FROM `xes_live_plan_class_stuids` WHERE class_id='{$classId}'";
        $StuIdstr=$this->db->query($sql_query)->fetch()['stu_ids'];
        //返回去重后的学生id
        return array_unique(explode(',',$StuIdstr));
    }

}