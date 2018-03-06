<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * ActionColumn is a column for the [[GridView]] widget that displays buttons for viewing and manipulating the items.
 *
 * To add an ActionColumn to the gridview, add it to the [[GridView::columns|columns]] configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         '__class' => \yii\grid\ActionColumn::class,
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * For more details and usage information on ActionColumn, see the [guide article on data widgets](guide:output-data-widgets).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionColumn extends Column
{
    /**
     * {@inheritdoc}
     */
    public $headerOptions = ['class' => 'action-column'];
    /**
     * @var string the ID of the controller that should handle the actions specified here.
     * If not set, it will use the currently active controller. This property is mainly used by
     * [[urlCreator]] to create URLs for different actions. The value of this property will be prefixed
     * to each action name to form the route of the action.
     */
    public $controller;
    /**
     * @var string the template used for composing each cell in the action column.
     * Tokens enclosed within curly brackets are treated as controller action IDs (also called *button names*
     * in the context of action column). They will be replaced by the corresponding button rendering callbacks
     * specified in [[buttons]]. For example, the token `{view}` will be replaced by the result of
     * the callback `buttons['view']`. If a callback cannot be found, the token will be replaced with an empty string.
     *
     * As an example, to only have the view, and update button you can add the ActionColumn to your GridView columns as follows:
     *
     * ```php
     * ['__class' => \yii\grid\ActionColumn::class, 'template' => '{view} {update}'],
     * ```
     *
     * @see buttons
     */
    public $template = '{view} {update} {delete}';
    /**
     * @var array button rendering callbacks. The array keys are the button names (without curly brackets),
     * and the values are the corresponding button rendering callbacks. The callbacks should use the following
     * signature:
     *
     * ```php
     * function ($url, $model, $key) {
     *     // return the button HTML code
     * }
     * ```
     *
     * where `$url` is the URL that the column creates for the button, `$model` is the model object
     * being rendered for the current row, and `$key` is the key of the model in the data provider array.
     *
     * You can add further conditions to the button, for example only display it, when the model is
     * editable (here assuming you have a status field that indicates that):
     *
     * ```php
     * [
     *     'update' => function ($url, $model, $key) {
     *         return $model->status === 'editable' ? Html::a('Update', $url) : '';
     *     },
     * ],
     * ```
     */
    public $buttons = [];
    /**
     * @var array visibility conditions for each button. The array keys are the button names (without curly brackets),
     * and the values are the boolean true/false or the anonymous function. When the button name is not specified in
     * this array it will be shown by default.
     * The callbacks must use the following signature:
     *
     * ```php
     * function ($model, $key, $index) {
     *     return $model->status === 'editable';
     * }
     * ```
     *
     * Or you can pass a boolean value:
     *
     * ```php
     * [
     *     'update' => \Yii::$app->user->can('update'),
     * ],
     * ```
     *
     * Note that visibility of the particular button can be also controlled via 'visible' key in its array configuration.
     * Usage of [[visibleButtons]] make sense mostly for buttons specified via `Closure`.
     *
     * @since 2.0.7
     */
    public $visibleButtons = [];
    /**
     * @var callable a callback that creates a button URL using the specified model information.
     * The signature of the callback should be the same as that of [[createUrl()]]
     * Since 2.0.10 it can accept additional parameter, which refers to the column instance itself:
     *
     * ```php
     * function (string $action, mixed $model, mixed $key, integer $index, ActionColumn $this) {
     *     //return string;
     * }
     * ```
     *
     * If this property is not set, button URLs will be created using [[createUrl()]].
     */
    public $urlCreator;
    /**
     * @var array html options to be applied to the array button configuration at [[renderButton()]].
     * @since 2.0.4
     */
    public $buttonOptions = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->buttons = ArrayHelper::merge($this->defaultButtons(), $this->buttons);
    }

    /**
     * Initializes the default button rendering callbacks.
     * @since 2.1.0
     */
    protected function defaultButtons()
    {
        return [
            'view' => [
                'icon' => 'eye-open',
                'options' => [
                    'title' => Yii::t('yii', 'View'),
                    'aria-label' => Yii::t('yii', 'View'),
                ],
            ],
            'update' => [
                'icon' => 'pencil',
                'options' => [
                    'title' => Yii::t('yii', 'Update'),
                    'aria-label' => Yii::t('yii', 'Update'),
                ],
            ],
            'delete' => [
                'icon' => 'trash',
                'options' => [
                    'title' => Yii::t('yii', 'Delete'),
                    'aria-label' => Yii::t('yii', 'Delete'),
                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    'data-method' => 'post',
                ],
            ],
        ];
    }

    /**
     * Creates a URL for the given action and model.
     * This method is called for each button and each row.
     * @param string $action the button name (or action ID)
     * @param \yii\db\ActiveRecordInterface|array $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index the current row index
     * @return string the created URL
     */
    public function createUrl($action, $model, $key, $index)
    {
        if (is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index, $this);
        }

        $params = is_array($key) ? $key : ['id' => (string) $key];
        $params[0] = $this->controller ? $this->controller . '/' . $action : $action;

        return Url::toRoute($params);
    }

    /**
     * {@inheritdoc}
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
            $name = $matches[1];

            return $this->renderButton($name, $model, $key, $index);
        }, $this->template);
    }

    /**
     * Renders button.
     * @param string $name button name.
     * @param \yii\db\ActiveRecordInterface|array $model the data model.
     * @param mixed $key the key associated with the data model.
     * @param int $index the current row index.
     * @return string rendered HTML.
     * @throws InvalidConfigException on invalid button format.
     * @since 2.1.0
     */
    protected function renderButton($name, $model, $key, $index)
    {
        if (isset($this->visibleButtons[$name])) {
            $isVisible = $this->visibleButtons[$name] instanceof \Closure
                ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                : $this->visibleButtons[$name];
        } else {
            $isVisible = true;
        }

        if (!$isVisible || !isset($this->buttons[$name])) {
            return '';
        }
        $button = $this->buttons[$name];

        if ($button instanceof \Closure) {
            $url = $this->createUrl($name, $model, $key, $index);
            return call_user_func($button, $url, $model, $key);
        }
        if (!is_array($button)) {
            throw new InvalidConfigException("Button should be either a Closure or array configuration.");
        }

        $button = array_merge($button, $this->buttonOptions);

        // Visibility :
        if (isset($button['visible'])) {
            if ($button['visible'] instanceof \Closure) {
                if (!call_user_func($button['visible'], $model, $key, $index)) {
                    return '';
                }
            } elseif (!$button['visible']) {
                return '';
            }
        }

        // URL :
        if (isset($button['url'])) {
            if (is_string($button['url'])) {
                $url = $button['url'];
            } else {
                $url = call_user_func($button['url'], $name, $model, $key, $index);
            }
        } else {
            $url = $this->createUrl($name, $model, $key, $index);
        }

        // label :
        if (isset($button['label'])) {
            $label = $button['label'];

            if (isset($button['encode'])) {
                $encodeLabel = $button['encode'];
                unset($button['encode']);
            } else {
                $encodeLabel = true;
            }
            if ($encodeLabel) {
                $label = Html::encode($label);
            }
        } else {
            $label = '';
        }

        // icon :
        if (isset($button['icon'])) {
            $icon = $button['icon'];
            $label = $this->renderIcon($icon) . (empty($label) ? '' : '&nbsp;' . $label);
        }

        $options = array_merge(ArrayHelper::getValue($button, 'options', []), $this->buttonOptions);

        return Html::a($label, $url, $options);
    }

    /**
     * Renders icon HTML representation.
     * @param string $iconName name of icon to be rendered.
     * @return string icon HTML.
     * @since 2.1.0
     */
    protected function renderIcon($iconName)
    {
        return Html::tag('span', '', ['class' => "icon icon-$iconName"]);
    }
}
