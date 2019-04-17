<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',    
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => require(__DIR__ . '/db.php'),
        'db_logger' => require(__DIR__ . '/db_logger.php'),        
    ],
    "params"=>require(__DIR__ . '/params.php'),
];
