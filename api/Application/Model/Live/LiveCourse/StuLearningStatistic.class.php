<?php
/**
 * 学生学习情况统计数据
 * ligaofeng
 */
namespace Xes\Application\Model\Live\LiveCourse;

use Xes\Lib\Config;
use Xes\Lib\Model;

class StuLearningStatistic extends Model
{
    protected $db = null;

    public function __construct()
    {
        $dbConfig = Config::get('\Xes\Application\Config\Storage\Default\live');
        $this->db = \Xes\Lib\G('\Xes\Lib\Pdodb', $dbConfig);
    }

    /****
     * 查询 xes_live_student_learning_statistics 表
    ***/
    public function searchStuLearningStatistic($params)
    {
        $where = ' WHERE 1=1 ';
        if (!empty($params['stu_id'])){
            $where .= " AND `stu_id` IN ({$params['stu_id']})";
        }
        if (!empty($params['start_time'])){
            $where .= " AND `create_time` >= '{$params['start_time']}'";
        }
        if (!empty($params['end_time'])){
            $where .= " AND `create_time` <= '{$params['end_time']}'";
        }
        if (!empty($params['plan_id'])){
            $where .= " AND `plan_id` IN ({$params['plan_id']})";
        }
        if (!empty($params['class_id'])){
            $where .= " AND `class_id` IN ({$params['class_id']})";
        }
        if (!empty($params['group_id'])){
            $where .= " AND `group_id` IN ({$params['group_id']})";
        }
        $order = "";
        if (!empty($params['order']) && $params['order'] = 'DSEC'){
            $order .= " ORDER BY create_time DESC ";
        }
        $limit = "";
        if (!empty($params['curpage']) && !empty($params['perpage'])){
            $limit = " LIMIT " . ($params['curpage'] - 1) * $params['perpage'] . " , " . $params['perpage'];
        }
        $sql = <<<XES
        SELECT * FROM xes_live_student_learning_statistics 
                $where $order $limit
XES;
        $result = $this->db->g($sql,array());
        return $result;
    }

}