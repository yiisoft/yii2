<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $version
 * @property array $properties
 */
class Document extends ActiveRecord
{
    public function optimisticLock()
    {
        return 'version';
    }
}
