<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Closure;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * RadioButtonColumn displays a column of radio buttons in a grid view.
 *
 * To add a RadioButtonColumn to the [[GridView]], add it to the [[GridView::columns|columns]] configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         '__class' => \yii\grid\RadioButtonColumn::class,
 *         'radioOptions' => function ($model) {
 *              return [
 *                  'value' => $model['value'],
 *                  'checked' => $model['value'] == 2
 *              ];
 *          }
 *     ],
 * ]
 * ```
 *
 * @author Kirk Hansen <hanski07@luther.edu>
 * @since 2.0.11
 */
class RadioButtonColumn extends Column
{
    /**
     * @var string the name of the input radio button input fields.
     */
    public $name = 'radioButtonSelection';
    /**
     * @var array|\Closure the HTML attributes for the radio buttons. This can either be an array of
     * attributes or an anonymous function ([[Closure]]) returning such an array.
     *
     * The signature of the function should be as follows: `function ($model, $key, $index, $column)`
     * where `$model`, `$key`, and `$index` refer to the model, key and index of the row currently being rendered
     * and `$column` is a reference to the [[RadioButtonColumn]] object.
     *
     * A function may be used to assign different attributes to different rows based on the data in that row.
     * Specifically if you want to set a different value for the radio button you can use this option
     * in the following way (in this example using the `name` attribute of the model):
     *
     * ```php
     * 'radioOptions' => function ($model, $key, $index, $column) {
     *     return ['value' => $model->attribute];
     * }
     * ```
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $radioOptions = [];


    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException if [[name]] is not set.
     */
    public function init()
    {
        parent::init();
        if (empty($this->name)) {
            throw new InvalidConfigException('The "name" property must be set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return parent::renderDataCellContent($model, $key, $index);
        }

        if ($this->radioOptions instanceof Closure) {
            $options = call_user_func($this->radioOptions, $model, $key, $index, $this);
        } else {
            $options = $this->radioOptions;
            if (!isset($options['value'])) {
                $options['value'] = is_array($key) ? json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $key;
            }
        }
        $checked = $options['checked'] ?? false;
        return Html::radio($this->name, $checked, $options);
    }
}
