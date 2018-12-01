<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidConfigException 表示由不正确的对象配置导致的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidConfigException extends Exception
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
