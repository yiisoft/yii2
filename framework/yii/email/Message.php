<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

use yii\email\swift\Message as SwiftMessage;

/**
 * Message provides the email message sending functionality.
 *
 * Usage:
 * ~~~
 * $email = new Message();
 * $email->from = 'sender@domain.com';
 * $email->to = 'receiver@domain.com';
 * $email->subject = 'Message Subject';
 * $email->text = 'Message Content';
 * $email->send();
 * ~~~
 *
 * This particular class uses 'SwiftMailer' library to perform the message sending.
 * Note: you can replace usage of this class by your own one, using [[Yii::$classMap]]:
 * ~~~
 * Yii::$classMap['yii\email\Message'] = '/path/to/my/email/Message.php'
 * ~~~
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Message extends SwiftMessage {}