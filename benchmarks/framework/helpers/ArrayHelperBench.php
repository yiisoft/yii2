<?php

use yii\helpers\ArrayHelper;

/**
 * @Revs(1000)
 * @Iterations(5)
 */
class TimeConsumerBench
{
    public function benchMergeTooArray()
    {
        $a = [
            'vendorPath' => '/yiisoft/yii2-app-advanced/vendor',
            'components' => [
                'cache' => [
                    'class' => 'yii\\caching\\FileCache',
                ],
            ],
        ];
        $b = [
            'components' => [
                'db' => [
                    'class' => 'yii\\db\\Connection',
                    'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8',
                ],
                'mailer' => [
                    'class' => 'yii\\swiftmailer\\Mailer',
                    'viewPath' => '@common/mail',
                ],
            ],
        ];
    
    
        ArrayHelper::merge($a, $b);
    }
    
    public function benchMergeMoreArray()
    {
        $a = [
            'vendorPath' => '/yiisoft/yii2-app-advanced/vendor',
            'components' => [
                'cache' => [
                    'class' => 'yii\\caching\\FileCache',
                ],
            ],
        ];
        $b = [
            'components' => [
                'db' => [
                    'class' => 'yii\\db\\Connection',
                    'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8',
                ],
                'mailer' => [
                    'class' => 'yii\\swiftmailer\\Mailer',
                    'viewPath' => '@common/mail',
                ],
            ],
        ];
        $c = [
            'id' => 'app-frontend',
            'basePath' => '/yiisoft/yii2-app-advanced/frontend',
            'bootstrap' => [
                0 => 'log',
            ],
            'controllerNamespace' => 'frontend\\controllers',
            'components' => [
                'request' => [
                    'csrfParam' => '_csrf-frontend',
                ],
                'user' => [
                    'identityClass' => 'common\\models\\User',
                    'enableAutoLogin' => true,
                    'identityCookie' => [
                        'name' => '_identity-frontend',
                        'httpOnly' => true,
                    ],
                ],
                'session' => [
                    'name' => 'advanced-frontend',
                ],
                'log' => [
                    'traceLevel' => 0,
                    'targets' => [
                        0 => [
                            'class' => 'yii\\log\\FileTarget',
                            'levels' => [
                                0 => 'error',
                                1 => 'warning',
                            ],
                        ],
                    ],
                ],
                'errorHandler' => [
                    'errorAction' => 'site/error',
                ],
            ],
            'params' => [
                'adminEmail' => 'admin@example.com',
                'supportEmail' => 'support@example.com',
                'user.passwordResetTokenExpire' => 3600,
            ],
        ];
        $d = [
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'secret',
                ],
            ],
        ];
    
        ArrayHelper::merge($a, $b, $c, $d);
    }
}
