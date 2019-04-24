<?php

/**
 * 辅导班日志相关操作
 *
 * @author zhongjingqin
 */

namespace Xes\Application\Model\Live\LiveCourse;

use Xes\Lib\Config;
use Xes\Lib\Model;

class LiveClassLog extends Model
{

    protected $db = null;

    public function __construct()
    {
        $dbConfig = Config::get('\Xes\Application\Config\Storage\Default\live');
        $this->db = \Xes\Lib\G('\Xes\Lib\Pdodb', $dbConfig);
    }

    /**
     * 复杂查询
     *
     * @param type $where
     * @param type $order
     * @param type $limit
     */
    public function searchLiveClassLogLists($where, $order, $limit)
    {
        $search = array();
        $sql = "SELECT * FROM `xes_live_class_logs` $where $order $limit";
        $params = array();
        $result['list'] = $this->db->g($sql, $params);

        $sql = "SELECT count(`id`) as `total` FROM `xes_live_class_logs` $where ";
        $total = $this->db->g($sql, $params);
        $result['total'] = $total[0]['total'];
        return $result;
    }

}
