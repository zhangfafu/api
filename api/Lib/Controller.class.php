<?php

/**
 * 控制器基础类
 */

namespace Xes\Lib;

abstract class Controller
{

    /**
     * 保存客户端传递的变量，包括GET/POST/PUT等
     *
     * @access proteted
     * @var array
     * @name $params
     */
    protected $params;

    public function __construct()
    {

    }


    /**
     * 设置客户端传递参数
     *
     * @params array $params 参数
     * @return array [int:status, mixted string]
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

}
