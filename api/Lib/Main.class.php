<?php

namespace Xes\Lib;

use Xes\Lib\Core;

include_once XES_ROOT_DIR . '/Lib/Core.class.php';
include_once XES_ROOT_DIR . '/Lib/Functions.class.php';

class Main
{
    private $logDir = [];
    private $stime;
    private $etime;

    public function __construct()
    {
        $this->stime = getMicrotime();

        \Xes\Lib\MyErrors::run();
        \Xes\Lib\MyException::run();
    }

    public function start()
    {
        register_shutdown_function(function() {
            $last = error_get_last();
            if ($last['type'] == E_ERROR || $last['type'] == E_CORE_ERROR || $last['type'] == E_COMPILE_ERROR || $last['type'] == E_PARSE) {
                if (class_exists('\Xes\Service\Log\XesLog')) {
                    $mes = MyErrors::getLogMes($last['message']);   
                    \Xes\Service\Log\XesLog::error($mes, $last['file'], $last['line']);
                }
            }

            if (isset($this->logDir['print']) && $this->logDir['print'] == true) {
                Core::$data['errors'] = \Xes\Lib\MyErrors::getAllInfos();
                if ($last['type'] == E_ERROR) {
                    $last['debugtrace'] = debug_backtrace();
                    array_unshift(Core::$data['errors'], $last);
                }
                Core::$data['exception'] = \Xes\Lib\MyException::getAllInfos();
                Core::$data['total']['mysql'] = Core::$time['mysql'];
                Core::$data['total']['redis'] = Core::$time['redis'];
                Core::$data['total']['curl'] = Core::$curl;
            }
            $this->etime = getMicrotime();

            if (isset(Core::$data['params']['debug']) && Core::$data['params']['debug'] == 2) {
                echo msgpack_serialize(Core::$data);
            } else {
                $data = array(
                    'data' => Core::$data['data'],
                );
				//var_dump($data);
                echo msgpack_serialize($data);
            }
        });

        $this->dispatch();
    }

    protected function dispatch()
    {
        $params = explode('/', trim($_GET['url'], '/'));
        $route = \Xes\Lib\R('\Xes\Application\Config\Route');
        if (isset($route[$params[0]])) {
            $controller = $route[$params[0]];
        } else {
            switch (count($params)) {
                case '2':
                    $controller = '\Xes\Application\Controller\Www\\' . $params[0];
                    break;
                case '3':
                    $controller = '\Xes\Application\Controller\\' . $params[2] . '\\' . $params[0];
                    break;
            }
        }

        Core::$data['url'] = $_GET['url'];
        Core::$data['params'] = $_REQUEST;
        Core::$data['API_ip'] = !empty($_SERVER['REMOTE_ADDR']) ? : '';
        Core::$data['data'] = \Xes\Lib\G($controller)->setParams($_REQUEST)->$params[1]();
        return;
    }

   
    public function __destruct()
    {

    }
}
