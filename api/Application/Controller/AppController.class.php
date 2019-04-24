<?php

namespace Xes\Application\Controller;

use \Xes\Lib\Controller;
use \Xes\Lib\Config;

class AppController extends Controller
{

    /**
     * 接口return错误消息统一处理方法
     * @params string $msg 错误消息提示
     * @return array $data
     */
    protected function getErrorData($msg)
    {
        $data['stat'] = 0;
        $data['rows'] = 0;
        $data['data'] = $msg;
        return $data;
    }

  
    /**
     * 计算时间差
     * @return float
     */
    protected function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}
