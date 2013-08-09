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
use yii\data\Sort;
use yii\helpers\Html;

/**
 * LinkSorter renders a list of sort links for the given sort definition.
 *
 * LinkSorter will generate a hyperlink for every attribute declared in [[sort]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LinkSorter extends Widget
{
	/**
	 * @var Sort the sort definition
	 */
	public $sort;
	/**
	 * @var array HTML attributes for the sorter container tag.
	 */
	public $options = array('class' => 'sorter');


	/**
	 * Initializes the sorter.
	 */
	public function init()
	{
		if ($this->sort === null) {
			throw new InvalidConfigException('The "sort" property must be set.');
		}
	}

	/**
	 * Executes the widget.
	 * This method renders the sort links.
	 */
	public function run()
	{
		echo $this->renderSortLinks();
	}

	/**
	 * Renders the sort links.
	 * @return string the rendering result
	 */
	protected function renderSortLinks()
	{
		$links = array();
		foreach (array_keys($this->sort->attributes) as $name) {
			$links[] = $this->sort->link($name);
		}
		return Html::ul($links, array('encode' => false));
	}
}
