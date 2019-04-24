<?php
if (empty($_GET)) {
    $_GET['url'] = 'test/index';
}

define('XES_ROOT_DIR', __DIR__);

require XES_ROOT_DIR . '/Lib/Autoload.class.php';

use \Xes\Lib\Main;
$a = new Main(XES_ROOT_DIR);
$a->start();
