<?php

/**
 * 系列讲座相关
 *
 */

namespace Xes\Application\Model\Live\Lecture;

use Xes\Lib\Config;
use Xes\Lib\Model;

class SeriesLecture extends Model
{

    protected $db = null;

    public function __construct()
    {
        $dbConfig = Config::get('\Xes\Application\Config\Storage\Default\learningclass');
        $this->db = \Xes\Lib\G('\Xes\Lib\Pdodb', $dbConfig);
    }

    /**
     * 获取直播系列讲座列表
     */
    public function seriesLectureLivesLists($params)
    {
        $limit = '';
        if (!empty($params['curpage']) && !empty($params['perpage']) && empty($params['isExcel'])) {
            $limit = "LIMIT " . ($params['curpage'] - 1) * $params['perpage'] . "," . $params['perpage'];
        }
        $order = " ORDER BY `id` DESC";
        $where = 'WHERE 1=1';

        if (!empty($params['id'])) {
            $where .= " AND id = {$params['id']}";
        }

        if (!empty($params['live_ids'])) {
            $str .= " AND (";
            $liveIdsArr = explode(',', $params['live_ids']);
            foreach ($liveIdsArr as $val) {
                $str .= " FIND_IN_SET({$val},live_ids) OR";
            }
            $str = trim($str, 'OR');
            $where .= ($str . ')');
        }

        if (isset($params['status']) && $params['status'] != 2) {
            $where .= " AND status = '{$params['status']}'";
        }

        $sql = <<<XES
            SELECT COUNT(*) AS `total` FROM `xes_series_lecture_lives` $where
XES;

        $countRes = $this->db->g($sql, array());
        $result['total'] = $countRes[0]['total'];

        $sql = <<<XES
            SELECT `id`,`name`,`live_ids`, `subject_ids`, `grade_digits`, `status`,`is_recommend`,`image_id`,`active_url`,`adv_image_id`  FROM `xes_series_lecture_lives`
        $where $order $limit
XES;

        $result['list'] = $this->db->g($sql, array());

        $statusArr = [0 => '已停用', 1 => '已启用'];
        foreach ($result['list'] as $key => $value) {
            $result['list'][$key]['status'] = $statusArr[$value['status']];
        }
        return $result;
    }

    /**
     * 更新直播系列讲座信息
     */
    public function updateSeriesLectureLive($param)
    {
        $sql = <<<XES
            UPDATE `xes_series_lecture_lives`
            SET `name` = :name, `live_ids` = :live_ids, `image_id` = :image_id, `active_url` = :active_url, `adv_image_id` = :adv_image_id, `is_recommend` = :is_recommend
            WHERE `id` = :id
XES;

        $params = array(
            array('id', $param['id'], \PDO::PARAM_INT),
            array('name', $param['name'], \PDO::PARAM_INT),
            array('live_ids', $param['live_ids'], \PDO::PARAM_INT),
            array('image_id', $param['image_id'], \PDO::PARAM_INT),
            array('is_recommend', $param['is_recommend'], \PDO::PARAM_INT),
            array('active_url', $param['active_url'], \PDO::PARAM_INT),
            array('adv_image_id', $param['adv_image_id'], \PDO::PARAM_INT),
        );
        if (!$this->db->ud($sql, $params)) {
            return false;
        }

        return true;
    }

    /**
     * 冻结直播系列讲座信息
     */
    public function freezeSeriesLectureLives($param)
    {
        $sql = <<<XES
            UPDATE `xes_series_lecture_lives`
            SET `status` = 0
            WHERE `id` = :id
XES;

        $params = array(
            array('id', $param['id'], \PDO::PARAM_INT)
        );
        if (!$this->db->ud($sql, $params)) {
            return false;
        }

        return true;
    }

    /**
     *
     * 插入直播系列讲座信息
     */
    public function addSeriesLectureLive($data)
    {
        $result = $this->db->c('xes_series_lecture_lives', $data);
        return $this->db->lastInsertId();
    }

    /**
     * 获取直播系列讲座信息
     */
    public function seriesLectureLivesInfo($params)
    {

        $where = 'WHERE 1=1';

        if (!empty($params['id'])) {
            $where .= " AND id in ({$params['id']})";
        }

        $sql = <<<XES
            SELECT * FROM `xes_series_lecture_lives`
        $where
XES;

        $result = $this->db->g($sql, array());

        return $result;
    }

    /**
     * 获取所有状态正常的直播系列讲座信息
     */
    public function getNormalSeriesInfo()
    {
        $where = 'WHERE 1=1 AND `status` = 1';
        if (!empty($params['start_id'])) {
            $where .= " AND `id` >= " . $params['start_id'];
        }
        if (!empty($params['end_id'])) {
            $where .= " AND `id` <= " . $params['end_id'];
        }
        $sql = <<<XES
            SELECT * FROM `xes_series_lecture_lives`
            $where
XES;

        $result = $this->db->g($sql, array());

        return $result;
    }

}
