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
 * Stub {@see Model} exercising scenario-scoped validation rules with an excluded scenario.
 */
final class ComplexModel2 extends Model
{
    public function rules(): array
    {
        return [
            [['id'], 'required', 'except' => ['suddenlyUnexpectedScenario']],
            [['name', 'description'], 'filter', 'filter' => 'trim'],
            [['is_disabled'], 'boolean', 'on' => ['administration']],
        ];
    }
}
