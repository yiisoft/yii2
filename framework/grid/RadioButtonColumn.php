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
 * RadioButtonColumn 在网格视图中显示一列单选按钮。
 *
 * 要将 RadioButtonColumn 添加到 [[GridView]] 中，请将其添加到 [[GridView::columns|columns]] 的配置中，如下：
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => 'yii\grid\RadioButtonColumn',
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
     * @var string 单选按钮输入字段的名称。
     */
    public $name = 'radioButtonSelection';
    /**
     * @var array|\Closure 单选按钮的 HTML 属性。
     * 这可以是属性数组或返回此类数组的匿名函数（[[Closure]]）。
     *
     * 该函数的写法应该如下：`function ($model, $key, $index, $column)`
     * 其中，`$model`，`$key` 和 `$index` 表示当前渲染行的模型，键和索引，
     * `$column` 是对 [[RadioButtonColumn]] 对象的引用。
     *
     * 函数可用于根据该行中的数据将不同的属性分配给不同的行。
     * 具体来说，如果希望为单选按钮设置不同的值，
     * 可以按照如下的方法使用此选项：（在本例中使用模型的 `name` 属性）。
     *
     * ```php
     * 'radioOptions' => function ($model, $key, $index, $column) {
     *     return ['value' => $model->attribute];
     * }
     * ```
     *
     * @see \yii\helpers\Html::renderTagAttributes() 有关于如果渲染属性的详细信息。
     */
    public $radioOptions = [];


    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException 如果 [[name]] 没有设置抛出的异常。
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
        $checked = isset($options['checked']) ? $options['checked'] : false;
        return Html::radio($this->name, $checked, $options);
    }
}
