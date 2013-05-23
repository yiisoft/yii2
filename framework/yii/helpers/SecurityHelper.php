<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * SecurityHelper provides a set of methods to handle common security-related tasks.
 *
 * In particular, SecurityHelper supports the following features:
 *
 * - Encryption/decryption: [[encrypt()]] and [[decrypt()]]
 * - Data tampering prevention: [[hashData()]] and [[validateData()]]
 * - Password validation: [[generatePasswordHash()]] and [[validatePassword()]]
 *
 * Additionally, SecurityHelper provides [[getSecretKey()]] to support generating
 * named secret keys. These secret keys, once generated, will be stored in a file
 * and made available in future requests.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Tom Worster <fsb@thefsb.org>
 * @since 2.0
 */
class SecurityHelper extends base\SecurityHelper
{
}
