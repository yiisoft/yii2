<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\Arrayable;
use yii\i18n\Formatter;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * DetailView displays the detail of a single data [[model]].
 *
 * DetailView is best used for displaying a model in a regular format (e.g. each model attribute
 * is displayed as a row in a table.) The model can be either an instance of [[Model]]
 * or an associative array.
 *
 * DetailView uses the [[attributes]] property to determines which model attributes
 * should be displayed and how they should be formatted.
 *
 * A typical usage of DetailView is as follows:
 *
 * ```php
 * echo DetailView::widget([
 *     'model' => $model,
 *     'attributes' => [
 *         'title',               // title attribute (in plain text)
 *         'description:html',    // description attribute in HTML
 *         [                      // the owner name of the model
 *             'label' => 'Owner',
 *             'value' => $model->owner->name,
 *         ],
 *         'created_at:datetime', // creation date formatted as datetime
 *     ],
 * ]);
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DetailView extends Widget
{
    /**
     * @var array|object the data model whose details are to be displayed. This can be a [[Model]] instance,
     * an associative array, an object that implements [[Arrayable]] interface or simply an object with defined
     * public accessible non-static properties.
     */
    public $model;
    /**
     * @var array a list of attributes to be displayed in the detail view. Each array element
     * represents the specification for displaying one particular attribute.
     *
     * An attribute can be specified as a string in the format of `attribute`, `attribute:format` or `attribute:format:label`,
     * where `attribute` refers to the attribute name, and `format` represents the format of the attribute. The `format`
     * is passed to the [[Formatter::format()]] method to format an attribute value into a displayable text.
     * Please refer to [[Formatter]] for the supported types. Both `format` and `label` are optional.
     * They will take default values if absent.
     *
     * An attribute can also be specified in terms of an array with the following elements:
     *
     * - `attribute`: the attribute name. This is required if either `label` or `value` is not specified.
     * - `label`: the label associated with the attribute. If this is not specified, it will be generated from the attribute name.
     * - `value`: the value to be displayed. If this is not specified, it will be retrieved from [[model]] using the attribute name
     *   by calling [[ArrayHelper::getValue()]]. Note that this value will be formatted into a displayable text
     *   according to the `format` option.
     * - `format`: the type of the value that determines how the value would be formatted into a displayable text.
     *   Please refer to [[Formatter]] for supported types.
     * - `visible`: whether the attribute is visible. If set to `false`, the attribute will NOT be displayed.
     * - `contentOptions`: the HTML attributes to customize value tag. For example: `['class' => 'bg-red']`.
     *   Please refer to [[\yii\helpers\BaseHtml::renderTagAttributes()]] for the supported syntax.
     * - `captionOptions`: the HTML attributes to customize label tag. For example: `['class' => 'bg-red']`.
     *   Please refer to [[\yii\helpers\BaseHtml::renderTagAttributes()]] for the supported syntax.
     */
    public $attributes;
    /**
     * @var string|callable the template used to render a single attribute. If a string, the token `{label}`
     * and `{value}` will be replaced with the label and the value of the corresponding attribute.
     * If a callback (e.g. an anonymous function), the signature must be as follows:
     *
     * ```php
     * function ($attribute, $index, $widget)
     * ```
     *
     * where `$attribute` refer to the specification of the attribute being rendered, `$index` is the zero-based
     * index of the attribute in the [[attributes]] array, and `$widget` refers to this widget instance.
     *
     * Since Version 2.0.10, the tokens `{captionOptions}` and `{contentOptions}` are available, which will represent
     * HTML attributes of HTML container elements for the label and value.
     */
    public $template = '<tr><th{captionOptions}>{label}</th><td{contentOptions}>{value}</td></tr>';
    /**
     * @var array the HTML attributes for the container tag of this widget. The `tag` option specifies
     * what container tag should be used. It defaults to `table` if not set.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'table table-striped table-bordered detail-view'];
    /**
     * @var array|Formatter the formatter used to format model attribute values into displayable texts.
     * This can be either an instance of [[Formatter]] or an configuration array for creating the [[Formatter]]
     * instance. If this property is not set, the `formatter` application component will be used.
     */
    public $formatter;


    /**
     * Initializes the detail view.
     * This method will initialize required property values.
     */
    public function init()
    {
        if ($this->model === null) {
            throw new InvalidConfigException('Please specify the "model" property.');
        }
        if ($this->formatter === null) {
            $this->formatter = Yii::$app->getFormatter();
        } elseif (is_array($this->formatter)) {
            $this->formatter = Yii::createObject($this->formatter);
        }
        if (!$this->formatter instanceof Formatter) {
            throw new InvalidConfigException('The "formatter" property must be either a Format object or a configuration array.');
        }
        $this->normalizeAttributes();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    /**
     * Renders the detail view.
     * This is the main entry of the whole detail view rendering.
     */
    public function run()
    {
        $rows = [];
        $i = 0;
        foreach ($this->attributes as $attribute) {
            $rows[] = $this->renderAttribute($attribute, $i++);
        }

        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'table');
        echo Html::tag($tag, implode("\n", $rows), $options);
    }

    /**
     * Renders a single attribute.
     * @param array $attribute the specification of the attribute to be rendered.
     * @param integer $index the zero-based index of the attribute in the [[attributes]] array
     * @return string the rendering result
     */
    protected function renderAttribute($attribute, $index)
    {
        if (is_string($this->template)) {
            $captionOptions = Html::renderTagAttributes(ArrayHelper::getValue($attribute, 'captionOptions', []));
            $contentOptions = Html::renderTagAttributes(ArrayHelper::getValue($attribute, 'contentOptions', []));
            return strtr($this->template, [
                '{label}' => $attribute['label'],
                '{value}' => $this->formatter->format($attribute['value'], $attribute['format']),
                '{captionOptions}' => $captionOptions,
                '{contentOptions}' =>  $contentOptions,
            ]);
        } else {
            return call_user_func($this->template, $attribute, $index, $this);
        }
    }

    /**
     * Normalizes the attribute specifications.
     * @throws InvalidConfigException
     */
    protected function normalizeAttributes()
    {
        if ($this->attributes === null) {
            if ($this->model instanceof Model) {
                $this->attributes = $this->model->attributes();
            } elseif (is_object($this->model)) {
                $this->attributes = $this->model instanceof Arrayable ? array_keys($this->model->toArray()) : array_keys(get_object_vars($this->model));
            } elseif (is_array($this->model)) {
                $this->attributes = array_keys($this->model);
            } else {
                throw new InvalidConfigException('The "model" property must be either an array or an object.');
            }
            sort($this->attributes);
        }

        foreach ($this->attributes as $i => $attribute) {
            if (is_string($attribute)) {
                if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/', $attribute, $matches)) {
                    throw new InvalidConfigException('The attribute must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"');
                }
                $attribute = [
                    'attribute' => $matches[1],
                    'format' => isset($matches[3]) ? $matches[3] : 'text',
                    'label' => isset($matches[5]) ? $matches[5] : null,
                ];
            }

            if (!is_array($attribute)) {
                throw new InvalidConfigException('The attribute configuration must be an array.');
            }

            if (isset($attribute['visible']) && !$attribute['visible']) {
                unset($this->attributes[$i]);
                continue;
            }

            if (!isset($attribute['format'])) {
                $attribute['format'] = 'text';
            }
            if (isset($attribute['attribute'])) {
                $attributeName = $attribute['attribute'];
                if (!isset($attribute['label'])) {
                    $attribute['label'] = $this->model instanceof Model ? $this->model->getAttributeLabel($attributeName) : Inflector::camel2words($attributeName, true);
                }
                if (!array_key_exists('value', $attribute)) {
                    $attribute['value'] = ArrayHelper::getValue($this->model, $attributeName);
                }
            } elseif (!isset($attribute['label']) || !array_key_exists('value', $attribute)) {
                throw new InvalidConfigException('The attribute configuration requires the "attribute" element to determine the value and display label.');
            }

            $this->attributes[$i] = $attribute;
        }
    }
}
