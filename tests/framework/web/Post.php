<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\base\BaseObject;

/**
 * @inheritdoc
 */
class Post extends BaseObject
{
    /**
     * @param int $id
     * @param string $title
     * @param array $config
     */
    public function __construct(public $id, public $title, $config = [])
    {
        parent::__construct($config);
    }
}
