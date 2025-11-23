<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * BaseListView is a base class for widgets displaying data from data provider
 * such as ListView and GridView.
 *
 * It provides features like sorting, paging and also filtering the data.
 *
 * For more details and usage information on BaseListView, see the [guide article on data widgets](guide:output-data-widgets).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class BaseListView extends Widget
{
    /**
     * @var array the HTML attributes for the container tag of the list view.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var \yii\data\DataProviderInterface the data provider for the view. This property is required.
     */
    public $dataProvider;
    /**
     * @var array the configuration for the pager widget. By default, [[LinkPager]] will be
     * used to render the pager. You can use a different widget class by configuring the "class" element.
     * Note that the widget must support the `pagination` property which will be populated with the
     * [[\yii\data\BaseDataProvider::pagination|pagination]] value of the [[dataProvider]] and will overwrite this value.
     */
    public $pager = [];
    /**
     * @var array the configuration for the sorter widget. By default, [[LinkSorter]] will be
     * used to render the sorter. You can use a different widget class by configuring the "class" element.
     * Note that the widget must support the `sort` property which will be populated with the
     * [[\yii\data\BaseDataProvider::sort|sort]] value of the [[dataProvider]] and will overwrite this value.
     */
    public $sorter = [];
    /**
     * @var string|null the HTML content to be displayed as the summary of the list view.
     * If you do not want to show the summary, you may set it with an empty string.
     *
     * The following tokens will be replaced with the corresponding values:
     *
     * - `{begin}`: the starting row number (1-based) currently being displayed
     * - `{end}`: the ending row number (1-based) currently being displayed
     * - `{count}`: the number of rows currently being displayed
     * - `{totalCount}`: the total number of rows available
     * - `{page}`: the page number (1-based) current being displayed
     * - `{pageCount}`: the number of pages available
     */
    public $summary;
    /**
     * @var array the HTML attributes for the summary of the list view.
     * The "tag" element specifies the tag name of the summary element and defaults to "div".
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $summaryOptions = ['class' => 'summary'];
    /**
     * @var bool whether to show an empty list view if [[dataProvider]] returns no data.
     * The default value is false which displays an element according to the [[emptyText]]
     * and [[emptyTextOptions]] properties.
     */
    public $showOnEmpty = false;
    /**
     * @var string|false|null the HTML content to be displayed when [[dataProvider]] does not have any data.
     * When this is set to `false` no extra HTML content will be generated.
     * The default value is the text "No results found." which will be translated to the current application language.
     * @see showOnEmpty
     * @see emptyTextOptions
     */
    public $emptyText;
    /**
     * @var array the HTML attributes for the emptyText of the list view.
     * The "tag" element specifies the tag name of the emptyText element and defaults to "div".
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $emptyTextOptions = ['class' => 'empty'];
    /**
     * @var string the layout that determines how different sections of the list view should be organized.
     * The following tokens will be replaced with the corresponding section contents:
     *
     * - `{summary}`: the summary section. See [[renderSummary()]].
     * - `{items}`: the list items. See [[renderItems()]].
     * - `{sorter}`: the sorter. See [[renderSorter()]].
     * - `{pager}`: the pager. See [[renderPager()]].
     */
    public $layout = "{summary}\n{items}\n{pager}";


    /**
     * Renders the data models.
     * @return string the rendering result.
     */
    abstract public function renderItems();

    /**
     * Initializes the view.
     */
    public function init()
    {
        parent::init();
        if ($this->dataProvider === null) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }
        if ($this->emptyText === null) {
            $this->emptyText = Yii::t('yii', 'No results found.');
        }
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        if ($this->showOnEmpty || $this->dataProvider->getCount() > 0) {
            $content = preg_replace_callback('/{\\w+}/', function ($matches) {
                $content = $this->renderSection($matches[0]);

                return $content === false ? $matches[0] : $content;
            }, $this->layout);
        } else {
            $content = $this->renderEmpty();
        }

        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        echo Html::tag($tag, $content, $options);
    }

    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `{summary}`, `{items}`.
     * @return string|bool the rendering result of the section, or false if the named section is not supported.
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{summary}':
                return $this->renderSummary();
            case '{items}':
                return $this->renderItems();
            case '{pager}':
                return $this->renderPager();
            case '{sorter}':
                return $this->renderSorter();
            default:
                return false;
        }
    }

    /**
     * Renders the HTML content indicating that the list view has no data.
     * @return string the rendering result
     * @see emptyText
     */
    public function renderEmpty()
    {
        if ($this->emptyText === false) {
            return '';
        }
        $options = $this->emptyTextOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        return Html::tag($tag, $this->emptyText, $options);
    }

    /**
     * Renders the summary text.
     */
    public function renderSummary()
    {
        $count = $this->dataProvider->getCount();
        if ($count <= 0) {
            return '';
        }
        $summaryOptions = $this->summaryOptions;
        $tag = ArrayHelper::remove($summaryOptions, 'tag', 'div');
        if (($pagination = $this->dataProvider->getPagination()) !== false) {
            $totalCount = $this->dataProvider->getTotalCount();
            $begin = $pagination->getPage() * $pagination->pageSize + 1;
            $end = $begin + $count - 1;
            if ($begin > $end) {
                $begin = $end;
            }
            $page = $pagination->getPage() + 1;
            $pageCount = $pagination->pageCount;
            if (($summaryContent = $this->summary) === null) {
                return Html::tag($tag, Yii::t('yii', 'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.', [
                        'begin' => $begin,
                        'end' => $end,
                        'count' => $count,
                        'totalCount' => $totalCount,
                        'page' => $page,
                        'pageCount' => $pageCount,
                    ]), $summaryOptions);
            }
        } else {
            $begin = $page = $pageCount = 1;
            $end = $totalCount = $count;
            if (($summaryContent = $this->summary) === null) {
                return Html::tag($tag, Yii::t('yii', 'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.', [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ]), $summaryOptions);
            }
        }

        if ($summaryContent === '') {
            return '';
        }

        return Html::tag($tag, Yii::$app->getI18n()->format($summaryContent, [
            'begin' => $begin,
            'end' => $end,
            'count' => $count,
            'totalCount' => $totalCount,
            'page' => $page,
            'pageCount' => $pageCount,
        ], Yii::$app->language), $summaryOptions);
    }

    /**
     * Renders the pager.
     * @return string the rendering result
     */
    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        if ($pagination === false || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        $pager = $this->pager;
        /** @var LinkPager $class */
        $class = ArrayHelper::remove($pager, 'class', LinkPager::class);
        $pager['pagination'] = $pagination;
        $pager['view'] = $this->getView();

        return $class::widget($pager);
    }

    /**
     * Renders the sorter.
     * @return string the rendering result
     */
    public function renderSorter()
    {
        $sort = $this->dataProvider->getSort();
        if ($sort === false || empty($sort->attributes) || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        $sorter = $this->sorter;
        /** @var LinkSorter $class */
        $class = ArrayHelper::remove($sorter, 'class', LinkSorter::class);
        $sorter['sort'] = $sort;
        $sorter['view'] = $this->getView();

        return $class::widget($sorter);
    }
}
