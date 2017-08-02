<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * LinkPager displays a list of hyperlinks that lead to different pages of target.
 *
 * LinkPager works with a [[Pagination]] object which specifies the total number
 * of pages and the current page number.
 *
 * Note that LinkPager only generates the necessary HTML markups. In order for it
 * to look like a real pager, you should provide some CSS styles for it.
 * With the default configuration, LinkPager should look good using Twitter Bootstrap CSS framework.
 *
 * For more details and usage information on LinkPager, see the [guide article on pagination](guide:output-pagination).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LinkPager extends Widget
{
    /**
     * @var Pagination the pagination object that this pager is associated with.
     * You must set this property in order to make LinkPager work.
     */
    public $pagination;
    /**
     * @var array HTML attributes for the pager container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'pagination'];
    /**
     * @var array HTML attributes which will be applied to all link containers
     * @since 2.0.13
     */
    public $linkContainerOptions = [];
    /**
     * @var array HTML attributes for the link in a pager container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $linkOptions = [];
    /**
     * @var string the CSS class for the each page button.
     * @since 2.0.7
     */
    public $pageCssClass;
    /**
     * @var string the CSS class for the "first" page button.
     */
    public $firstPageCssClass = 'first';
    /**
     * @var string the CSS class for the "last" page button.
     */
    public $lastPageCssClass = 'last';
    /**
     * @var string the CSS class for the "previous" page button.
     */
    public $prevPageCssClass = 'prev';
    /**
     * @var string the CSS class for the "next" page button.
     */
    public $nextPageCssClass = 'next';
    /**
     * @var string the CSS class for the active (currently selected) page button.
     */
    public $activePageCssClass = 'active';
    /**
     * @var string the CSS class for the disabled page buttons.
     */
    public $disabledPageCssClass = 'disabled';
    /**
     * @var array the options for the disabled tag to be generated inside the disabled list element.
     * In order to customize the html tag, please use the tag key.
     *
     * ```php
     * $disabledListItemSubTagOptions = ['tag' => 'div', 'class' => 'disabled-div'];
     * ```
     * @since 2.0.11
     */
    public $disabledListItemSubTagOptions = [];
    /**
     * @var int maximum number of page buttons that can be displayed. Defaults to 10.
     */
    public $maxButtonCount = 10;
    /**
     * @var string|bool the label for the "next" page button. Note that this will NOT be HTML-encoded.
     * If this property is false, the "next" page button will not be displayed.
     */
    public $nextPageLabel = '&raquo;';
    /**
     * @var string|bool the text label for the previous page button. Note that this will NOT be HTML-encoded.
     * If this property is false, the "previous" page button will not be displayed.
     */
    public $prevPageLabel = '&laquo;';
    /**
     * @var string|bool the text label for the "first" page button. Note that this will NOT be HTML-encoded.
     * If it's specified as true, page number will be used as label.
     * Default is false that means the "first" page button will not be displayed.
     */
    public $firstPageLabel = false;
    /**
     * @var string|bool the text label for the "last" page button. Note that this will NOT be HTML-encoded.
     * If it's specified as true, page number will be used as label.
     * Default is false that means the "last" page button will not be displayed.
     */
    public $lastPageLabel = false;
    /**
     * @var bool whether to register link tags in the HTML header for prev, next, first and last page.
     * Defaults to `false` to avoid conflicts when multiple pagers are used on one page.
     * @see http://www.w3.org/TR/html401/struct/links.html#h-12.1.2
     * @see registerLinkTags()
     */
    public $registerLinkTags = false;
    /**
     * @var bool Hide widget when only one page exist.
     */
    public $hideOnSinglePage = true;
    /**
     * @var bool whether to render current page button as disabled.
     * @since 2.0.12
     */
    public $disableCurrentPageButton = false;


    /**
     * Initializes the pager.
     */
    public function init()
    {
        if ($this->pagination === null) {
            throw new InvalidConfigException('The "pagination" property must be set.');
        }
    }

    /**
     * Executes the widget.
     * This overrides the parent implementation by displaying the generated page buttons.
     */
    public function run()
    {
        if ($this->registerLinkTags) {
            $this->registerLinkTags();
        }
        echo $this->renderPageButtons();
    }

    /**
     * Registers relational link tags in the html header for prev, next, first and last page.
     * These links are generated using [[\yii\data\Pagination::getLinks()]].
     * @see http://www.w3.org/TR/html401/struct/links.html#h-12.1.2
     */
    protected function registerLinkTags()
    {
        $view = $this->getView();
        foreach ($this->pagination->getLinks() as $rel => $href) {
            $view->registerLinkTag(['rel' => $rel, 'href' => $href], $rel);
        }
    }

    /**
     * Renders the page buttons.
     * @return string the rendering result
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, $this->disableCurrentPageButton && $i == $currentPage, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'ul');
        return Html::tag($tag, implode("\n", $buttons), $options);
    }

    /**
     * Renders a page button.
     * You may override this method to customize the generation of page buttons.
     * @param string $label the text label for the button
     * @param int $page the page number
     * @param string $class the CSS class for the page button.
     * @param bool $disabled whether this page button is disabled
     * @param bool $active whether this page button is active
     * @return string the rendering result
     */
    protected function renderPageButton($label, $page, $class, $disabled, $active)
    {
        $options = $this->linkContainerOptions;
        $linkWrapTag = ArrayHelper::remove($options, 'tag', 'li');
        Html::addCssClass($options, empty($class) ? $this->pageCssClass : $class);

        if ($active) {
            Html::addCssClass($options, $this->activePageCssClass);
        }
        if ($disabled) {
            Html::addCssClass($options, $this->disabledPageCssClass);
            $tag = ArrayHelper::remove($this->disabledListItemSubTagOptions, 'tag', 'span');

            return Html::tag($linkWrapTag, Html::tag($tag, $label, $this->disabledListItemSubTagOptions), $options);
        }
        $linkOptions = $this->linkOptions;
        $linkOptions['data-page'] = $page;

        return Html::tag($linkWrapTag, Html::a($label, $this->pagination->createUrl($page), $linkOptions), $options);
    }

    /**
     * @return array the begin and end pages that need to be displayed.
     */
    protected function getPageRange()
    {
        $currentPage = $this->pagination->getPage();
        $pageCount = $this->pagination->getPageCount();

        $beginPage = max(0, $currentPage - (int) ($this->maxButtonCount / 2));
        if (($endPage = $beginPage + $this->maxButtonCount - 1) >= $pageCount) {
            $endPage = $pageCount - 1;
            $beginPage = max(0, $endPage - $this->maxButtonCount + 1);
        }

        return [$beginPage, $endPage];
    }
}
