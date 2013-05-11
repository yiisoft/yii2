<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\base\Widget;
use yii\web\Pagination;

/**
 * ListPager displays a drop-down list that contains options leading to different pages.
 *
 * ListPager works with a [[Pagination]] object which specifies the totally number
 * of pages and the current page number.
 *
 * Note that ListPager requires JavaScript to work. You should consider using [[LinkPager]]
 * if you want to make your page work without JavaScript.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ListPager extends Widget
{
	/**
	 * @var Pagination the pagination object that this pager is associated with.
	 * You must set this property in order to make ListPager work.
	 */
	public $pagination;
	/**
	 * @var array HTML attributes for the drop-down list tag. The following options are specially handled:
	 *
	 * - prompt: string, a prompt text to be displayed as the first option.
	 *
	 * The rest of the options will be rendered as the attributes of the resulting tag. The values will
	 * be HTML-encoded using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
	 */
	public $options = array();
	/**
	 * @var string the template used to render the label for each list option.
	 * The token "{page}" will be replaced with the actual page number (1-based).
	 */
	public $template = '{page}';


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
		$pageCount = $this->pagination->pageCount;
		$currentPage = $this->pagination->getPage();

		$pages = array();
		for ($i = 0; $i < $pageCount; ++$i) {
			$pages[$this->pagination->createUrl($i)] = $this->generatePageText($i);
		}
		$selection = $this->pagination->createUrl($currentPage);

		if (!isset($this->options['onchange'])) {
			$this->options['onchange'] = "if (this.value != '') { window.location = this.value; };";
		}

		echo Html::dropDownList(null, $selection, $pages, $this->options);
	}

	/**
	 * Generates the label of the list option for the specified page number.
	 * You may override this method to customize the option display.
	 * @param integer $page zero-based page number
	 * @return string the list option for the page number
	 */
	protected function generatePageText($page)
	{
		return strtr($this->template, array(
			'{page}' => $page + 1,
		));
	}

}