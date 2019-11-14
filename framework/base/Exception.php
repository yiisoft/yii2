<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Exception 表示用于所有目的的通用异常。
 *
 * 有关异常的更多详细信息和使用信息，请参阅 [有关错误处理的指南文章](guide:runtime-handling-errors)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Exception extends \Exception
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Exception';
    }
}
