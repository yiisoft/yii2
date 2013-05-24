<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;
use yii\helpers\base\ArrayHelper;
use yii\helpers\Html;

/**
 * Tabs renders a Tab bootstrap javascript component.
 *
 * For example:
 *
 * ```php
 * echo Tabs::widget(array(
 *     'options' => array('class'=>'nav-tabs'),
 *     'items' => array(
 *         array(
 *             'header' => 'One',
 *             'content' => 'Anim pariatur cliche...',
 *         ),
 *         array(
 *             'header' => 'Two',
 *             'headerOptions' => array(...),
 *             'content' => 'Anim pariatur cliche...',
 *             'options' => array('id'=>'myveryownID'),
 *         ),
 *         array(
 *             'header' => 'Dropdown',
 *             'items' => array(
 *                  array(
 *                      'header' => '@Dropdown1',
 *                      'content' => 'Anim pariatur cliche...',
 *                  ),
 *             ),
 *         ),
 *     ),
 * ));
 * ```
 *
 * @see http://twitter.github.io/bootstrap/javascript.html#tabs
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Tabs extends Widget
{
	/**
	 * @var array list of tabs in the tabs widget. Each array element represents a single
	 * tab with the following structure:
	 *
	 * ```php
	 * array(
	 *     // required, the header (HTML) of the tab
	 *     'header' => 'Tab label',
	 *     // optional the HTML attributes of the tab header `LI` tag container
	 *     'headerOptions'=> array(...),
	 *     // required, the content (HTML) of the tab
	 *     'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
	 *     // optional the HTML attributes of the tab content container
	 *     'options'=> array(...),
	 *     // optional, an array of items so to dipslay a dropdown menu on the tab header
	 *     // ***Important*** if `items` is set, then `content` will be ignored
	 *     'items'=> array(...)
	 * )
	 * ```
	 */
	public $items = array();
	/**
	 * @var int keeps track of the tabs count so to provide a correct id in case it has not been specified.
	 */
	protected $counter = 0;


	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$this->addCssClass($this->options, 'nav');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderHeaders($this->items, $this->options) . "\n";
		$this->counter = 0; // reset tab counter
		echo Html::beginTag('div', array('class' => 'tab-content')) . "\n";
		echo $this->renderContents($this->items) . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerPlugin('tab');
	}

	/**
	 * @param array $items the items to render in the header.
	 * @param array $options the HTML attributes of the menu container.
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 */
	protected function renderHeaders($items, $options = array())
	{
		$headers = array();

		for ($i = 0, $count = count($items); $i < $count; $i++) {
			$item = $items[$i];
			if (!isset($item['header'])) {
				throw new InvalidConfigException("The 'header' option is required.");
			}
			$headerOptions = ArrayHelper::getValue($item, 'headerOptions', array());
			if ($this->counter === 0) {
				$this->addCssClass($headerOptions, 'active');
			}
			if (isset($item['items'])) {
				$this->getView()->registerAssetBundle("yii/bootstrap/dropdown");
				$this->addCssClass($headerOptions, 'dropdown');
				$headers[] = Html::tag(
					'li',
					Html::a($item['header'] . ' <b class="caret"></b>', "#", array(
						'class' => 'dropdown-toggle',
						'data-toggle' => 'dropdown'
					)) .
					$this->renderHeaders($item['items'], array('class' => 'dropdown-menu')),
					$headerOptions
				);
			} else {
				$contentOptions = ArrayHelper::getValue($item, 'options', array());
				$id = ArrayHelper::getValue($contentOptions, 'id', $this->options['id'] . '-tab' . $this->counter++);
				$headers[] = Html::tag('li', Html::a($item['header'], "#$id", array('data-toggle' => 'tab')), $headerOptions);
			}
		}

		return Html::tag('ul', implode("\n", $headers), $options);
	}

	/**
	 * Renders tabs contents as specified on [[items]].
	 * @param array $items the items to get the contents from.
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 */
	protected function renderContents($items)
	{
		$contents = array();
		foreach ($items as $item) {
			if (!isset($item['content']) && !isset($item['items'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}
			$options = ArrayHelper::getValue($item, 'options', array());
			$this->addCssClass($options, 'tab-pane');

			if ($this->counter === 0) {
				$this->addCssClass($options, 'active');
			}
			if (isset($item['items'])) {
				$contents[] = $this->renderContents($item['items']);
			} else {
				$options['id'] = ArrayHelper::getValue($options, 'id', $this->options['id'] . '-tab' . $this->counter++);
				$contents[] = Html::tag('div', $item['content'], $options);
			}
		}

		return implode("\n", $contents);
	}
}