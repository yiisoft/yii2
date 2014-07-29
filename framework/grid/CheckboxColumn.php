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
 * CheckboxColumn displays a column of checkboxes in a grid view.
 *
 * To add a CheckboxColumn to the [[GridView]], add it to the [[GridView::columns|columns]] configuration as follows:
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
 * Users may click on the checkboxes to select rows of the grid. The selected rows may be
 * obtained by calling the following JavaScript code:
 *
 * ~~~
 * var keys = $('#grid').yiiGridView('getSelectedRows');
 * // keys is an array consisting of the keys associated with the selected rows
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CheckboxColumn extends Column
{
    /**
     * @var string the name of the input checkbox input fields. This will be appended with `[]` to ensure it is an array.
     */
    public $name = 'selection';
    /**
     * @var array HTML attributes for the checkboxes.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $checkboxOptions = [];
    /**
     * @var bool whether it is possible to select multiple rows. Defaults to `true`.
     */
    public $multiple = true;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException if [[name]] is not set.
     */
    public function init()
    {
        parent::init();
        if (empty($this->name)) {
            throw new InvalidConfigException('The "name" property must be set.');
        }
        if (substr_compare($this->name, '[]', -2)) {
            $this->name .= '[]';
        }
    }

    /**
     * Renders the header cell content.
     * The default implementation simply renders [[header]].
     * This method may be overridden to customize the rendering of the header cell.
     * @return string the rendering result
     */
    protected function renderHeaderCellContent()
    {
        $name = rtrim($this->name, '[]') . '_all';
        $id = $this->grid->options['id'];
        $options = json_encode([
            'name' => $this->name,
            'multiple' => $this->multiple,
            'checkAll' => $name,
        ]);
        $this->grid->getView()->registerJs("jQuery('#$id').yiiGridView('setSelectionColumn', $options);");

        if ($this->header !== null || !$this->multiple) {
            return parent::renderHeaderCellContent();
        } else {
            return Html::checkBox($name, false, ['class' => 'select-on-check-all']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->checkboxOptions instanceof Closure) {
            $options = call_user_func($this->checkboxOptions, $model, $key, $index, $this);
        } else {
            $options = $this->checkboxOptions;
            if (!isset($options['value'])) {
                $options['value'] = is_array($key) ? json_encode($key) : $key;
            }
        }

        return Html::checkbox($this->name, !empty($options['checked']), $options);
    }
}
