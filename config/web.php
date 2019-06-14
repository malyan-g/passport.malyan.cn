<?php

require(__DIR__ . '/const.php');
$config = [
    'id' => 'passport',
    'basePath' => dirname(__DIR__),
    'language'   => 'zh-CN',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '2lp18Q7XzHsSkbOrNfE4jLzYXPUmypMg',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'identityCookie' => ['domain' => DOMAIN, 'name' => '_identity-passport', 'httpOnly' => true],
            'enableAutoLogin' => true
        ],
        'session' => [
            'cookieParams' => ['domain' => DOMAIN, 'lifetime' => 0],
            'timeout' => 3600,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.\
            //这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.mxhichina.com',
                'username' => 'passport@malyan.cn',
                'password' => 'MALIANG2baipei',
                'port' => '465',
                'encryption' => 'ssl',
            ],
            'messageConfig'=>[
                'charset'=>'UTF-8',
                'from'=>['passport@malyan.cn' => 'Malyan']
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require __DIR__ . '/db.php',
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'suffix' => '.html',
            'rules' => [
                '<action:\w+>' => 'site/<action>',
                //'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],

    ],
    'params' => require __DIR__ . '/params.php',
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
