<?php

namespace yiiunit\data\ar;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $version
 */
class Document extends ActiveRecord
{
    public function optimisticLock()
    {
        return 'version';
    }
}