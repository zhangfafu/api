<?php

/**
 * 
 *
 * @author 钟景钦
 */

namespace Xes\Application\Model\Live\LiveVideo;

use Xes\Lib\Config;
use Xes\Lib\Model;

class LiveVideoLogs extends Model
{

    protected $db = null;

    public function __construct()
    {
        $dbConfig = Config::get('\Xes\Application\Config\Storage\Default\live');
        $this->db = \Xes\Lib\G('\Xes\Lib\Pdodb', $dbConfig);
    }

    /**
     * 文科组获取客户端最新版本
     */
    public function getArtClientVer($platform)
    {
        $sql = "SELECT * FROM `xes_live_clients` WHERE platform = $platform ORDER BY id DESC LIMIT 1";
        $result = $this->db->g($sql);
        return $result[0];
    }

    /**
     * 复杂查询
     *
     * @param type $where
     * @param type $order
     * @param type $limit
     */
    public function searchLiveVideoLogsLists($where, $order, $limit)
    {
        $search = array();
        $sql = "SELECT * FROM `xes_live_video_logs` $where $order $limit";
        $params = array();
        $result['list'] = $this->db->g($sql, $params);
//        $result['sql'] = $sql;
        $sql = "SELECT count(`id`) as `total` FROM `xes_live_video_logs` $where ";
        $total = $this->db->g($sql, $params);
        $result['total'] = $total[0]['total'];
        return $result;
    }

}
