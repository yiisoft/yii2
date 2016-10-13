<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * The ListView widget is used to display data from data
 * provider. Each data model is rendered using the view
 * specified.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ListView extends BaseListView
{
    /**
     * @var array the HTML attributes for the container of the rendering result of each data model.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * If "tag" is false, it means no container element will be rendered.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $itemOptions = [];
    /**
     * @var string|callable the name of the view for rendering each data item, or a callback (e.g. an anonymous function)
     * for rendering each data item. If it specifies a view name, the following variables will
     * be available in the view:
     *
     * - `$model`: mixed, the data model
     * - `$key`: mixed, the key value associated with the data item
     * - `$index`: integer, the zero-based index of the data item in the items array returned by [[dataProvider]].
     * - `$widget`: ListView, this widget instance
     *
     * Note that the view name is resolved into the view file by the current context of the [[view]] object.
     *
     * If this property is specified as a callback, it should have the following signature:
     *
     * ```php
     * function ($model, $key, $index, $widget)
     * ```
     */
    public $itemView;
    /**
     * @var array additional parameters to be passed to [[itemView]] when it is being rendered.
     * This property is used only when [[itemView]] is a string representing a view name.
     */
    public $viewParams = [];
    /**
     * @var string the HTML code to be displayed between any two consecutive items.
     */
    public $separator = "\n";
    /**
     * @var array the HTML attributes for the container tag of the list view.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'list-view'];
    /**
     * @var Closure an anonymous function that is called once BEFORE rendering each data model.
     * It should have the following signature:
     *
     * ```php
     * function ($model, $key, $index, $widget)
     * ```
     *
     * - `$model`: the current data model being rendered
     * - `$key`: the key value associated with the current data model
     * - `$index`: the zero-based index of the data model in the model array returned by [[dataProvider]]
     * - `$widget`: the ListView object
     *
     * The return result of the function will be rendered directly.
     * @since 2.0.10
     */
    public $beforeItem;
    /**
     * @var Closure an anonymous function that is called once AFTER rendering each data model.
     * It should have the similar signature as [[beforeItem]]. The return result of the function
     * will be rendered directly.
     * @since 2.0.10
     */
    public $afterItem;


    /**
     * Renders all data models.
     * @return string the rendering result
     */
    public function renderItems()
    {
        $models = $this->dataProvider->getModels();
        $keys = $this->dataProvider->getKeys();
        $rows = [];
        foreach (array_values($models) as $index => $model) {
            $key = $keys[$index];
            if ($this->beforeItem !== null) {
                $row = call_user_func($this->beforeItem, $model, $key, $index, $this);
                if ($row !== null && $row !== false) {
                    $rows[] = $row;
                }
            }

            $rows[] = $this->renderItem($model, $key, $index);

            if ($this->afterItem !== null) {
                $row = call_user_func($this->afterItem, $model, $key, $index, $this);
                if ($row !== null && $row !== false) {
                    $rows[] = $row;
                }
            }
        }

        return implode($this->separator, $rows);
    }

    /**
     * Renders a single data model.
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key value associated with the data model
     * @param integer $index the zero-based index of the data model in the model array returned by [[dataProvider]].
     * @return string the rendering result
     */
    public function renderItem($model, $key, $index)
    {
        if ($this->itemView === null) {
            $content = $key;
        } elseif (is_string($this->itemView)) {
            $content = $this->getView()->render($this->itemView, array_merge([
                'model' => $model,
                'key' => $key,
                'index' => $index,
                'widget' => $this,
            ], $this->viewParams));
        } else {
            $content = call_user_func($this->itemView, $model, $key, $index, $this);
        }
        $options = $this->itemOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        $options['data-key'] = is_array($key) ? json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string) $key;

        return Html::tag($tag, $content, $options);
    }
}
