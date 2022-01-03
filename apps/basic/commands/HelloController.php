<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";
    }
    
    /**
     * This command hash any string into Password hash.
     * @param string $str the message to be echoed.
     */
    public function actionHash($str = 'password', $salt='')
    {
        // the following is how the creation of new user is. In other words, how the password is hashed in the User model.
        echo password_hash(Yii::$app->params['salt'].$str.$salting, PASSWORD_BCRYPT, ['cost' => 12])."\n";
    }
}
