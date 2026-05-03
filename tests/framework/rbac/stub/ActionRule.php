<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac\stub;

use yii\rbac\Rule;

/**
 * Description of ActionRule.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ActionRule extends Rule
{
    public $name = 'action_rule';
    public $action = 'read';

    /**
     * Private and protected properties to ensure that serialized object
     * does not get corrupted after saving into the DB because of null-bytes
     * in the string.
     *
     * @see https://github.com/yiisoft/yii2/issues/10176
     * @see https://github.com/yiisoft/yii2/issues/12681
     */
    private $somePrivateProperty;
    protected $someProtectedProperty;

    public function execute($user, $item, $params)
    {
        return $this->action === 'all' || $this->action === $params['action'];
    }
}
