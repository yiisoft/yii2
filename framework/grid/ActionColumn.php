<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * ActionColumn 是 [[GridView]] 小部件的列，该小部件显示用于查看和操作项目的按钮。
 *
 * 要将 ActionColumn 添加到 gridview，请将其添加到 [[GridView::columns|columns]] 配置中，如下所示：
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => ActionColumn::className(),
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * 关于 ActionColumn 更多的细节和用法，请参阅 [guide article on data widgets](guide:output-data-widgets)。
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
     * @var string 应该处理此处指定的操作的控制器的 ID。
     * 如果没有设置，将使用当前有效的控制器。
     * 此属性主要是由 [[urlCreator]] 用于为不同的操作创建 URLs。
     * 此属性的值将作为每个操作名称的前缀，以形成操作的路径。
     */
    public $controller;
    /**
     * @var string 用于组合操作列中每个单元格的模板。
     * 括在大括号的标记被视为控制器操作 IDs（在操作列上的上下文中也称为 *按钮名称*）。
     * 它们将被 [[buttons]] 中指定的相应按钮渲染回调替换。
     * 例如，令牌 `{view}` 将被回调 `buttons['view']` 的结果所取代。
     * 如果找不到回调，则令牌将替换为空字符串。
     *
     * 例如，要只有视图和更新按钮，你可以将 ActionColumn 添加到 GridView 列，如下所示：
     *
     * ```php
     * ['class' => 'yii\grid\ActionColumn', 'template' => '{view} {update}'],
     * ```
     *
     * @see buttons
     */
    public $template = '{view} {update} {delete}';
    /**
     * @var array 按钮渲染的回调。
     * 数组键是按钮名称（没有大括号），并且值是相应的按钮渲染的回调。
     * 回调应该像以下来实现：
     *
     * ```php
     * function ($url, $model, $key) {
     *     // return the button HTML code
     * }
     * ```
     *
     * 其中 `$url` 是列为按钮创建的 URL，`$model` 是为当前行渲染的模型对象，
     * `$key` 是数据提供程序数组中模型的键。
     *
     * 你可以为该按钮添加更多的条件，
     * 例如仅在模型可编辑的时候显示它（假设你有一个指示该状态的字段）：
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
    /** @var array 每个按钮的可见性条件。
     * 数组键是按钮名称（没有大括号），值是布尔值 true/false 或者匿名函数。
     * 如果未在此数组中指定按钮名称，则默认情况下将显示该名称。
     * 回调应该像以下来实现：
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
     * @since 2.0.7
     */
    public $visibleButtons = [];
    /**
     * @var callable 使用指定的模型信息创建按钮 URL 的回调。
     * 回调的写法应该与 [[createUrl()]] 的写法相同
     * 从 2.0.10 版本开始，它可以接受附加参数，它引用实例本身：
     *
     * ```php
     * function (string $action, mixed $model, mixed $key, integer $index, ActionColumn $this) {
     *     //return string;
     * }
     * ```
     *
     * 如果未设置此属性，将使用 [[createUrl()]] 创建 URLs。
     */
    public $urlCreator;
    /**
     * @var array 要用于 [[initDefaultButton()|default button]] 的 html 选项。
     * @since 2.0.4
     */
    public $buttonOptions = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->initDefaultButtons();
    }

    /**
     * 初始化默认按钮渲染回调。
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'eye-open');
        $this->initDefaultButton('update', 'pencil');
        $this->initDefaultButton('delete', 'trash', [
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post',
        ]);
    }

    /**
     * 初始化单个按钮的默认按钮渲染回调。
     * @param string $name 在模板中写入的按钮名称
     * @param string $iconName Bootstrap glyphicon 类的一部分，使其独一无二
     * @param array $additionalOptions 一系列的附加选项
     * @since 2.0.11
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                switch ($name) {
                    case 'view':
                        $title = Yii::t('yii', 'View');
                        break;
                    case 'update':
                        $title = Yii::t('yii', 'Update');
                        break;
                    case 'delete':
                        $title = Yii::t('yii', 'Delete');
                        break;
                    default:
                        $title = ucfirst($name);
                }
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconName"]);
                return Html::a($icon, $url, $options);
            };
        }
    }

    /**
     * 为给定的操作和模型创建 URL。
     * 为每个按钮和每一行调用此方法。
     * @param string $action 按钮名称（或方法 ID）。
     * @param \yii\db\ActiveRecordInterface $model 数据模型
     * @param mixed $key 与数据模型相关的键
     * @param int $index 当前行索引
     * @return string 创建的 URL
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

            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                    ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                    : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            if ($isVisible && isset($this->buttons[$name])) {
                $url = $this->createUrl($name, $model, $key, $index);
                return call_user_func($this->buttons[$name], $url, $model, $key);
            }

            return '';
        }, $this->template);
    }
}
