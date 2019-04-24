<?php

/**
 * 直播测试题
 */

namespace Xes\Application\Model\Live\Test\LiarbryOne;

use Xes\Lib\Config;
use Xes\Lib\Model;

class LiveTest extends Model
{

    protected $db = null;

    public function __construct()
    {
        $dbConfig = Config::get('\Xes\Application\Config\Storage\Default\live');
        $this->db = \Xes\Lib\G('\Xes\Lib\Pdodb', $dbConfig);
    }
    
    /**
     * 通过直播场次id 获取测试题
     * @param type $planId 直播场次id
     */
    public function getTestByPlanId($planId)
    {
        $sql = <<<XES
			SELECT *
                FROM
                        `xes_live_plan_tests`
                WHERE
                        `plan_id` = :plan_id
                AND
                        `is_del` = 0
                ORDER BY 
                        `id` DESC
                limit 1
XES;
        $params = array(
            array('plan_id', $planId, \PDO::PARAM_INT)
        );

        return $this->db->g($sql, $params);
    }
    
    /**
     * 通过直播场次id 获取测试题
     * @param type $planId 直播场次id
     */
    public function delTestByPlanId($planId)
    {
        $sql = <<<XES
			DELETE FROM
                        `xes_live_plan_tests`
                WHERE
                        `plan_id` = :plan_id
XES;
        
        $params = array(
            array('plan_id', $planId, \PDO::PARAM_INT)
        );

        return $this->db->ud($sql, $params);
    }
    
    
    /** 
     * 根据测试题ID，获取测试题信息
     * @param  int $testId 测试题ID
     * @return [type]     [description]
     */
    public function getLiveTestInfo($testId)
    {
        $sql = <<<XES
            SELECT *
            FROM `xes_live_plan_tests`
            WHERE `id` = :id
XES;
        
        $params = array(
             array('id', $testId, \PDO::PARAM_INT)
            );
        return $this->db->g($sql, $params);
    }

}
