<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Closure;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\i18n\Formatter;
use yii\widgets\BaseListView;

/**
 * GridView 小部件用于在网格中显示数据。
 *
 * 它提供了诸如 [[sorter|sorting]]，[[pager|paging]] 以及 [[filterModel|filtering]] 数据的特性。
 *
 * 基本的用法如下所示：
 *
 * ```php
 * <?= GridView::widget([
 *     'dataProvider' => $dataProvider,
 *     'columns' => [
 *         'id',
 *         'name',
 *         'created_at:datetime',
 *         // ...
 *     ],
 * ]) ?>
 * ```
 *
 * 网格表的列根据 [[Column]] 类进行配置，
 * 这些类是通过 [[columns]] 配置的。
 *
 * 可以使用大量的属性自定义网格视图的外表。
 *
 * 关于 GridView 的更多细节和用法，请参阅 [guide article on data widgets](guide:output-data-widgets)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GridView extends BaseListView
{
    const FILTER_POS_HEADER = 'header';
    const FILTER_POS_FOOTER = 'footer';
    const FILTER_POS_BODY = 'body';

    /**
     * @var string 如果在配置数据列时没有显示指定类名，这默认数据列的类。
     * 默认为 'yii\grid\DataColumn'。
     */
    public $dataColumnClass;
    /**
     * @var string 网格表的标题
     * @see captionOptions
     */
    public $caption;
    /**
     * @var array 标题元素的 HTML 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关于如何渲染属性的详细信息。
     * @see caption
     */
    public $captionOptions = [];
    /**
     * @var array 属性的 HTML 元素的表格。
     * @see \yii\helpers\Html::renderTagAttributes() 有关于如何渲染属性的详细信息。
     */
    public $tableOptions = ['class' => 'table table-striped table-bordered'];
    /**
     * @var array the 网格视图的容器标签的 HTML 属性。
     * "tag" 元素指定容器元素的标记名称，默认为 "div"。
     * @see \yii\helpers\Html::renderTagAttributes() 有关于如何渲染属性的详细信息。
     */
    public $options = ['class' => 'grid-view'];
    /**
     * @var array 表标题行的 HTML 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关于如何渲染属性的详细信息。
     */
    public $headerRowOptions = [];
    /**
     * @var array 表格页脚行的 HTML 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关于如何渲染属性的详细信息。
     */
    public $footerRowOptions = [];
    /**
     * @var array|Closure 表主题行的 HTML 属性。
     * 这可以是指定所有主题行的公共 HTML 属性，也可以是返回 HTML 属性的数组的匿名函数。
     * 对于 [[dataProvider]] 返回的每个数据模型，将调用匿名函数一次。
     * 它应该像如下来实现：
     *
     * ```php
     * function ($model, $key, $index, $grid)
     * ```
     *
     * - `$model`：当前渲染的数据模型
     * - `$key`：与当前数据模型关联的键值
     * - `$index`：[[dataProvider]] 返回的模型数组中数据模型的从零开始的索引
     * - `$grid`：GridView 对象
     *
     * @see \yii\helpers\Html::renderTagAttributes() 有关于如何渲染属性的详细信息。
     */
    public $rowOptions = [];
    /**
     * @var Closure 在渲染每个数据模型之前调用一次的匿名函数。
     * 它应该类似 [[rowOptions]] 的写法。
     * 函数的返回结果将直接渲染。
     */
    public $beforeRow;
    /**
     * @var Closure 在渲染每个数据模型后调用一次的匿名函数。
     * 它应该类似 [[rowOptions]] 的写法。
     * 函数的返回结果将直接渲染。
     */
    public $afterRow;
    /**
     * @var bool 是否显示网格表的标题部分。
     */
    public $showHeader = true;
    /**
     * @var bool 是否显示网格表的页脚部分。
     */
    public $showFooter = false;
    /**
     * @var bool 如果 $showFooter 为 true，是否在 DOM 中将页脚放在 body 后面
     * @since 2.0.14
     */
    public $placeFooterAfterBody = false;
    /**
     * @var bool 如果 [[dataProvider]] 不返回数据，是否显示网格视图。
     */
    public $showOnEmpty = true;
    /**
     * @var array|Formatter 用于将模型属性格式化为可显示文本的格式化程序。
     * 这可以是 [[Formatter]] 的实例，也可以是用于创建 [[Formatter]] 实例的配置数组。
     * 如果为设置属性，"formatter" 程序组件将会被使用。
     */
    public $formatter;
    /**
     * @var array 网格列的配置。
     * 每个数组元素表示一个特定的网格列的配置。例如，
     *
     * ```php
     * [
     *     ['class' => SerialColumn::className()],
     *     [
     *         'class' => DataColumn::className(), // this line is optional
     *         'attribute' => 'name',
     *         'format' => 'text',
     *         'label' => 'Name',
     *     ],
     *     ['class' => CheckboxColumn::className()],
     * ]
     * ```
     *
     * 如果列是 [[DataColumn]] 的类，"class" 元素将会被省略。
     *
     * 作为快捷方式格式，
     * 可以使用字符串来指定包含 [[DataColumn::attribute|attribute]]，
     * [[DataColumn::format|format]] 和/或 [[DataColumn::label|label]] 选项：`"attribute:format:label"`。
     * 例如，上面的 "name" 列也可以指定为：`"name:text:Name"`。
     * "format" 和 "label" 都是可选的。如果不存在，它们将采用默认值。
     *
     * 使用快捷方式格式，在简单情况下，列的配置如下所示：
     *
     * ```php
     * [
     *     'id',
     *     'amount:currency:Total Amount',
     *     'created_at:datetime',
     * ]
     * ```
     *
     * 将 [[dataProvider]] 与活动记录一起使用时，你可以显示相关记录中的值，
     * 例如 `author` 关联的 `name` 属性：
     *
     * ```php
     * // shortcut syntax
     * 'author.name',
     * // full syntax
     * [
     *     'attribute' => 'author.name',
     *     // ...
     * ]
     * ```
     */
    public $columns = [];
    /**
     * @var string 当单元格的内容为空时显示 HTML。
     * 此属性用于渲染没有定义内容的单元格，
     * 例如，空页脚或过滤单元格。
     *
     * 注意如果数据项为 `null`，则 [[DataColumn]] 不会使用它。
     * 在这种情况下，
     * [[formatter]] 的 [[\yii\i18n\Formatter::nullDisplay|nullDisplay]] 属性将用于指示空数据值。
     */
    public $emptyCell = '&nbsp;';
    /**
     * @var \yii\base\Model 保留用户输入的过滤数据的模型。
     * 设置此属性后，网格视图将启用基于列的筛选。
     * 默认情况下，每个数据列都会在顶部显示一个文本字段，用户可以填写该字段以过滤数据。
     *
     * 请注意，为了用于显示用于过滤的输入字段，
     * 列必须设置其 [[DataColumn::attribute]] 属性，
     * 并且该属性应在 $filterModel 的当前场景中处于有效状态或具有 [[DataColumn::filter]] 设置为输入字段的 HTML 代码。
     *
     * 如果未设置此属性（null），则禁用过滤功能。
     */
    public $filterModel;
    /**
     * @var string|array 返回过滤结果的 URL。
     * 将调用 [[Url::to()]] 来规范化 URL。如果没有设置，将使用当前控制器方法。
     * 当用户更改任何过滤输入时，
     * 当前过滤输入将作为 GET 参数附加到此 URL。
     */
    public $filterUrl;
    /**
     * @var string 用于选择过滤器输入字段的附加 jQuery 选择器
     */
    public $filterSelector;
    /**
     * @var string 过滤器是否应显示在网格视图中。有效的值包含：
     *
     * - [[FILTER_POS_HEADER]]：过滤器将显示在每列的标题单元格的顶部。
     * - [[FILTER_POS_BODY]]：过滤器将显示在每列的标题单元格的正下方。
     * - [[FILTER_POS_FOOTER]]：过滤器将显示在每列的页脚单元格下方。
     */
    public $filterPosition = self::FILTER_POS_BODY;
    /**
     * @var array 过滤器行元素的 HTML 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $filterRowOptions = ['class' => 'filters'];
    /**
     * @var array 用于渲染过滤器错误摘要的选项。
     * 有关于如果指定选项的更多信息，请参阅 [[Html::errorSummary()]]。
     * @see renderErrors()
     */
    public $filterErrorSummaryOptions = ['class' => 'error-summary'];
    /**
     * @var array 渲染每个过滤器错误消息的选项。
     * 当在每个过滤器输入字段旁边渲染错误消息时，这主要由 [[Html::error()]] 使用。
     */
    public $filterErrorOptions = ['class' => 'help-block'];
    /**
     * @var bool 无论如何应用过滤器失去焦点。能够通过 yiiGridView JS 管理过滤器
     * @since 2.0.16
     */
    public $filterOnFocusOut = true;
    /**
     * @var string 确定应如何组织网格视图的不同部分的布局。
     * 以下标记将替换为相应的部分内容：
     *
     * - `{summary}`：摘要部分。参阅 [[renderSummary()]]。
     * - `{errors}`：过滤器模型错误摘要。参阅 [[renderErrors()]]。
     * - `{items}`：列表项。参阅 [[renderItems()]]。
     * - `{sorter}`：排序。参阅 [[renderSorter()]]。
     * - `{pager}`：分页。参阅 [[renderPager()]]。
     */
    public $layout = "{summary}\n{items}\n{pager}";


    /**
     * 初始化网格视图。
     * 此方法将初始化所需的属性值并实例化 [[columns]] 对象。
     */
    public function init()
    {
        parent::init();
        if ($this->formatter === null) {
            $this->formatter = Yii::$app->getFormatter();
        } elseif (is_array($this->formatter)) {
            $this->formatter = Yii::createObject($this->formatter);
        }
        if (!$this->formatter instanceof Formatter) {
            throw new InvalidConfigException('The "formatter" property must be either a Format object or a configuration array.');
        }
        if (!isset($this->filterRowOptions['id'])) {
            $this->filterRowOptions['id'] = $this->options['id'] . '-filters';
        }

        $this->initColumns();
    }

    /**
     * 运行小部件。
     */
    public function run()
    {
        $view = $this->getView();
        GridViewAsset::register($view);
        $id = $this->options['id'];
        $options = Json::htmlEncode(array_merge($this->getClientOptions(), ['filterOnFocusOut' => $this->filterOnFocusOut]));
        $view->registerJs("jQuery('#$id').yiiGridView($options);");
        parent::run();
    }

    /**
     * 渲染过滤器模型的验证器错误。
     * @return string 渲染结果。
     */
    public function renderErrors()
    {
        if ($this->filterModel instanceof Model && $this->filterModel->hasErrors()) {
            return Html::errorSummary($this->filterModel, $this->filterErrorSummaryOptions);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{errors}':
                return $this->renderErrors();
            default:
                return parent::renderSection($name);
        }
    }

    /**
     * 返回网格视图 JS 小部件的选项。
     * @return array 选项
     */
    protected function getClientOptions()
    {
        $filterUrl = isset($this->filterUrl) ? $this->filterUrl : Yii::$app->request->url;
        $id = $this->filterRowOptions['id'];
        $filterSelector = "#$id input, #$id select";
        if (isset($this->filterSelector)) {
            $filterSelector .= ', ' . $this->filterSelector;
        }

        return [
            'filterUrl' => Url::to($filterUrl),
            'filterSelector' => $filterSelector,
        ];
    }

    /**
     * 渲染网格视图的数据模型。
     * @return string 表的 HTML 代码
     */
    public function renderItems()
    {
        $caption = $this->renderCaption();
        $columnGroup = $this->renderColumnGroup();
        $tableHeader = $this->showHeader ? $this->renderTableHeader() : false;
        $tableBody = $this->renderTableBody();

        $tableFooter = false;
        $tableFooterAfterBody = false;
        
        if ($this->showFooter) {
            if ($this->placeFooterAfterBody) {
                $tableFooterAfterBody = $this->renderTableFooter();
            } else {
                $tableFooter = $this->renderTableFooter();
            }
        }

        $content = array_filter([
            $caption,
            $columnGroup,
            $tableHeader,
            $tableFooter,
            $tableBody,
            $tableFooterAfterBody,
        ]);

        return Html::tag('table', implode("\n", $content), $this->tableOptions);
    }

    /**
     * 渲染标题元素。
     * @return bool|string 渲染的标题元素或者如果没有渲染就返回 `false`。
     */
    public function renderCaption()
    {
        if (!empty($this->caption)) {
            return Html::tag('caption', $this->caption, $this->captionOptions);
        }

        return false;
    }

    /**
     * 渲染列组 HTML。
     * @return bool|string 列组的 HTML 或者没有渲染列组就返回 `false`。
     */
    public function renderColumnGroup()
    {
        foreach ($this->columns as $column) {
            /* @var $column Column */
            if (!empty($column->options)) {
                $cols = [];
                foreach ($this->columns as $col) {
                    $cols[] = Html::tag('col', '', $col->options);
                }

                return Html::tag('colgroup', implode("\n", $cols));
            }
        }

        return false;
    }

    /**
     * 渲染表头。
     * @return string 渲染结果。
     */
    public function renderTableHeader()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $cells[] = $column->renderHeaderCell();
        }
        $content = Html::tag('tr', implode('', $cells), $this->headerRowOptions);
        if ($this->filterPosition === self::FILTER_POS_HEADER) {
            $content = $this->renderFilters() . $content;
        } elseif ($this->filterPosition === self::FILTER_POS_BODY) {
            $content .= $this->renderFilters();
        }

        return "<thead>\n" . $content . "\n</thead>";
    }

    /**
     * 渲染表尾。
     * @return string 渲染结果。
     */
    public function renderTableFooter()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $cells[] = $column->renderFooterCell();
        }
        $content = Html::tag('tr', implode('', $cells), $this->footerRowOptions);
        if ($this->filterPosition === self::FILTER_POS_FOOTER) {
            $content .= $this->renderFilters();
        }

        return "<tfoot>\n" . $content . "\n</tfoot>";
    }

    /**
     * 渲染过滤器。
     * @return string 渲染结果。
     */
    public function renderFilters()
    {
        if ($this->filterModel !== null) {
            $cells = [];
            foreach ($this->columns as $column) {
                /* @var $column Column */
                $cells[] = $column->renderFilterCell();
            }

            return Html::tag('tr', implode('', $cells), $this->filterRowOptions);
        }

        return '';
    }

    /**
     * 渲染表主体。
     * @return string 渲染结果。
     */
    public function renderTableBody()
    {
        $models = array_values($this->dataProvider->getModels());
        $keys = $this->dataProvider->getKeys();
        $rows = [];
        foreach ($models as $index => $model) {
            $key = $keys[$index];
            if ($this->beforeRow !== null) {
                $row = call_user_func($this->beforeRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }

            $rows[] = $this->renderTableRow($model, $key, $index);

            if ($this->afterRow !== null) {
                $row = call_user_func($this->afterRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
        }

        if (empty($rows) && $this->emptyText !== false) {
            $colspan = count($this->columns);

            return "<tbody>\n<tr><td colspan=\"$colspan\">" . $this->renderEmpty() . "</td></tr>\n</tbody>";
        }

        return "<tbody>\n" . implode("\n", $rows) . "\n</tbody>";
    }

    /**
     * 使用给定的数据模型和键渲染表行。
     * @param mixed $model 渲染的数据模型
     * @param mixed $key 与数据模型相关联的键
     * @param int $index [[dataProvider]] 返回的模型数组中数据模型的从零开始的索引。
     * @return string 渲染结果
     */
    public function renderTableRow($model, $key, $index)
    {
        $cells = [];
        /* @var $column Column */
        foreach ($this->columns as $column) {
            $cells[] = $column->renderDataCell($model, $key, $index);
        }
        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;

        return Html::tag('tr', implode('', $cells), $options);
    }

    /**
     * 创建列对象并初始化它们。
     */
    protected function initColumns()
    {
        if (empty($this->columns)) {
            $this->guessColumns();
        }
        foreach ($this->columns as $i => $column) {
            if (is_string($column)) {
                $column = $this->createDataColumn($column);
            } else {
                $column = Yii::createObject(array_merge([
                    'class' => $this->dataColumnClass ?: DataColumn::className(),
                    'grid' => $this,
                ], $column));
            }
            if (!$column->visible) {
                unset($this->columns[$i]);
                continue;
            }
            $this->columns[$i] = $column;
        }
    }

    /**
     * 基于格式为 "attribute:format:label" 的字符串创建 [[DataColumn]] 对象。
     * @param string $text 列规范字符串
     * @return DataColumn 列实例
     * @throws InvalidConfigException 如果列的规范无效抛出的异常
     */
    protected function createDataColumn($text)
    {
        if (!preg_match('/^([^:]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new InvalidConfigException('The column must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"');
        }

        return Yii::createObject([
            'class' => $this->dataColumnClass ?: DataColumn::className(),
            'grid' => $this,
            'attribute' => $matches[1],
            'format' => isset($matches[3]) ? $matches[3] : 'text',
            'label' => isset($matches[5]) ? $matches[5] : null,
        ]);
    }

    /**
     * 此函数尝试从给定数据中猜测要显示的列
     * 如果没有明确指定 [[columns]]。
     */
    protected function guessColumns()
    {
        $models = $this->dataProvider->getModels();
        $model = reset($models);
        if (is_array($model) || is_object($model)) {
            foreach ($model as $name => $value) {
                if ($value === null || is_scalar($value) || is_callable([$value, '__toString'])) {
                    $this->columns[] = (string) $name;
                }
            }
        }
    }
}
