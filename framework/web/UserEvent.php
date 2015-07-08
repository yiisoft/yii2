<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Event;

/**
 * This event class is used for Events triggered by the [[User]] class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UserEvent extends Event
{
    /**
     * @var IdentityInterface the identity object associated with this event
     */
    public $identity;
    /**
     * @var boolean whether the login is cookie-based. This property is only meaningful
     * for [[User::EVENT_BEFORE_LOGIN]] and [[User::EVENT_AFTER_LOGIN]] events.
     */
    public $cookieBased;
    /**
     * @var integer $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     */
    public $duration;
    /**
     * @var boolean whether the login or logout should proceed.
     * Event handlers may modify this property to determine whether the login or logout should proceed.
     * This property is only meaningful for [[User::EVENT_BEFORE_LOGIN]] and [[User::EVENT_BEFORE_LOGOUT]] events.
     */
    public $isValid = true;
}
