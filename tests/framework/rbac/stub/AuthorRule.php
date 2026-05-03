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
 * Checks if authorID matches userID passed via params.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class AuthorRule extends Rule
{
    public $name = 'isAuthor';
    public $reallyReally = false;

    /**
     * {@inheritdoc}
     */
    public function execute($user, $item, $params)
    {
        return $params['authorID'] == $user;
    }
}
