<?php

namespace Xes\Application\Model;

use \Xes\Lib\Model;
use \Xes\Lib\Config;

class AppModel extends Model
{

    public function __construct()
    {
        $dbConfig = config::get('\Xes\Application\Config\Storage');
        echo "dbConfig: {$dbConfig}\n";
        return;
    }

}
