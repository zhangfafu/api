<?php

/**
 * 测试
 */

namespace Xes\Application\Controller\Www;

use Xes\Application\Controller\AppController;
use Xes\Lib\Core;
class Test extends AppController
{
	
    private	$_dbConfig = null;

	/**
     * 测试
	 */
	public function index()
	{
		//读取数据库配置
		$this->_dbConfig = \Xes\Lib\R('\Xes\Application\Config\Storage\Default');
		print_r($this->_dbConfig);

		return array('stat' => 1, 'rows' => 1, 'data' => 'test');
	}

   
}
