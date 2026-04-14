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
 * Stub {@see Model} exercising scenario-scoped validation rules.
 */
final class ComplexModel1 extends Model
{
    public $description;
    public $id;
    public $is_disabled;
    public $name;

    public function rules(): array
    {
        return [
            [['id'], 'required', 'except' => ['administration']],
            [['name', 'description'], 'filter', 'filter' => 'trim', 'skipOnEmpty' => true],
            [['is_disabled'], 'boolean', 'on' => ['administration']],
        ];
    }
}
