<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\helpers;

/**
 * Typed model to test `Html::activeListInput()`.
 *
 * @see \yiiunit\framework\helpers\HtmlTest
 */
class TypedModel extends \yii\base\Model
{
    /**
     * @var int[]
     */
    public array $ids = [];
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['ids'], 'integer', 'allowArray' => true],
        ];
    }
}
