<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

use yii\base\Model;

/**
 * Stub {@see Model} exposing a write-only magic property via `setPassword()`.
 */
final class WriteOnlyModel extends Model
{
    public $passwordHash;

    public function rules()
    {
        return [
            [['password'], 'safe'],
        ];
    }

    public function setPassword($pw): void
    {
        $this->passwordHash = $pw;
    }
}
