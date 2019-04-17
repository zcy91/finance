<?php

use console\behaviors\ResponseBehavior;

ini_set('date.timezone', 'PRC');
 
defined('YII_DEBUG') or define('YII_DEBUG', true);

defined('YII_ENV') or define('YII_ENV', 'prod');

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(__DIR__ . '/console/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
        require(__DIR__ . '/common/config/main.php'), require(__DIR__ . '/console/config/main.php')
);

$application = new yii\console\Application($config);

$responseBehavior = new ResponseBehavior();

$application->attachBehavior("response_behavior", $responseBehavior);
  
