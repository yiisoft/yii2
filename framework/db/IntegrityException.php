<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Exception 表示由违反 DB 约束引起的异常。
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class IntegrityException extends Exception
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Integrity constraint violation';
    }
}
