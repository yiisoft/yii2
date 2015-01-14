<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\rbac\DbManager;
use yii\base\InvalidConfigException;

/**
 * Base class for the ActiveRecord used in the rback module
 *
 * @author Angel (Faryshta) Guevara <angeldelcaos@gmail.com>
 * @since 2.0.2
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @var DbManager
     */
    protected $authManager;

    /**
     * @inheritdoc
     */
    public function init() {
        $this->authManager = \Yii::$app->getAuthManager();
        if (!$this->authManager instanceof DbManager) {
            throw InvalidConfigException(
                'You need to configure the "authManager" component '
                . 'to use database.'
            );
        }

        parent::init();
    }
}
