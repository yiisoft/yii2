<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Event;

/**
 * TransactionEvent represents events triggered by the [[Connection]] component.
 *
 * @author André Sousa <andrelvsousa@gmail.com>
 * @since 2.?
 */
class TransactionEvent extends Event
{
    /**
     * @var \yii\db\Transaction the transaction.
     */
    public $transaction;
}
