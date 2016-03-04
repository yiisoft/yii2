<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Symfony\Component\VarDumper\Caster\Caster;
use yii\db\ActiveRecord;

class TinkerCaster
{
    /**
     * Get an array representing the properties of a model.
     *
     * @param \yii\db\ActiveRecord $model
     * @return array
     */
    public static function castModel(ActiveRecord $model)
    {
        $attributes = array_merge(
            $model->getAttributes(), $model->getRelatedRecords()
        );
        $results = [];
        foreach ($attributes as $key => $value) {
            $results[Caster::PREFIX_VIRTUAL.$key] = $value;
        }
        return $results;
    }
}
