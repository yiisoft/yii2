<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

/**
 * LogRuntimeException 表示由日志传递问题引起的异常。
 *
 * @author Bizley <pawel@positive.codes>
 * @since 2.0.14
 */
class LogRuntimeException extends \yii\base\Exception
{
    /**
     * @return string 容易理解的异常名称
     */
    public function getName()
    {
        return 'Log Runtime';
    }
}
