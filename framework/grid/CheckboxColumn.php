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
use yii\helpers\Json;

/**
 * CheckboxColumn 在网格视图中显示一列复选框。
 *
 * 将 CheckboxColumn 添加到 [[GridView]]，请将其添加到 [[GridView::columns|columns]] 配置中，如下所示：
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => 'yii\grid\CheckboxColumn',
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * 用户可以单击复选框来选择网格的行。
 * 可以通过调用以下的 JavaScript 代码来获取所选行：
 *
 * ```javascript
 * var keys = $('#grid').yiiGridView('getSelectedRows');
 * // keys is an array consisting of the keys associated with the selected rows
 * ```
 *
 * 关于 CheckboxColumn 的更多细节和用法，请参阅 [guide article on data widgets](guide:output-data-widgets)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CheckboxColumn extends Column
{
    /**
     * @var string 复选框输入字段的名称。将附加 `[]` 以确保它是一个数组。
     */
    public $name = 'selection';
    /**
     * @var array|\Closure 复选框的 HTML 属性。
     * 这可以是属性数组，也可以是返回这样一个数组的匿名函数（[[Closure]]）。
     * 函数的写法应该为：`function ($model, $key, $index, $column)`。
     * 其中，`$model`，`$key` 和 `$index` 表示当前渲染行的模型，键和索引，
     * `$column` 是对 [[CheckboxColumn]] 对象的引用。
     * 可以使用函数基于该行中的数据将不同的属性分配给不同的行。
     * 具体来说，如果要为复选框设置不同的值，
     * 可以按照以下方式使用此选项（在此实例中使用模型的 `name` 属性）。
     *
     * ```php
     * 'checkboxOptions' => function ($model, $key, $index, $column) {
     *     return ['value' => $model->name];
     * }
     * ```
     *
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $checkboxOptions = [];
    /**
     * @var bool 是否可以选择多行。默认为 `true`。
     */
    public $multiple = true;
    /**
     * @var string 将用于查找复选框的 css 类。
     * @since 2.0.9
     */
    public $cssClass;


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
        if (substr_compare($this->name, '[]', -2, 2)) {
            $this->name .= '[]';
        }

        $this->registerClientScript();
    }

    /**
     * 渲染标题单元格内容。
     * 默认实现只是渲染 [[header]]。
     * 可以重写此方法来自定义标题单元格的渲染。
     * @return string 渲染结果
     */
    protected function renderHeaderCellContent()
    {
        if ($this->header !== null || !$this->multiple) {
            return parent::renderHeaderCellContent();
        }

        return Html::checkbox($this->getHeaderCheckBoxName(), false, ['class' => 'select-on-check-all']);
    }

    /**
     * {@inheritdoc}
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return parent::renderDataCellContent($model, $key, $index);
        }

        if ($this->checkboxOptions instanceof Closure) {
            $options = call_user_func($this->checkboxOptions, $model, $key, $index, $this);
        } else {
            $options = $this->checkboxOptions;
        }

        if (!isset($options['value'])) {
            $options['value'] = is_array($key) ? Json::encode($key) : $key;
        }

        if ($this->cssClass !== null) {
            Html::addCssClass($options, $this->cssClass);
        }

        return Html::checkbox($this->name, !empty($options['checked']), $options);
    }

    /**
     * 返回标题复选框名称。
     * @return string 标题复选框名称
     * @since 2.0.8
     */
    protected function getHeaderCheckBoxName()
    {
        $name = $this->name;
        if (substr_compare($name, '[]', -2, 2) === 0) {
            $name = substr($name, 0, -2);
        }
        if (substr_compare($name, ']', -1, 1) === 0) {
            $name = substr($name, 0, -1) . '_all]';
        } else {
            $name .= '_all';
        }

        return $name;
    }

    /**
     * 注册所需的 JavaScript。
     * @since 2.0.8
     */
    public function registerClientScript()
    {
        $id = $this->grid->options['id'];
        $options = Json::encode([
            'name' => $this->name,
            'class' => $this->cssClass,
            'multiple' => $this->multiple,
            'checkAll' => $this->grid->showHeader ? $this->getHeaderCheckBoxName() : null,
        ]);
        $this->grid->getView()->registerJs("jQuery('#$id').yiiGridView('setSelectionColumn', $options);");
    }
}
