<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace data\validators\models;

use yii\base\Model;

class FakedValidationTypedModel extends Model
{
    public ?UploadedFile $single = null;

    public array $multiple = [];
}
