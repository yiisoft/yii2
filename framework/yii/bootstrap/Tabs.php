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
 *             'dropdown' => array(
 *                  array(
 *                      'label' => 'DropdownA',
 *                      'content' => 'DropdownA, Anim pariatur cliche...',
 *                  ),
 *                  '-', // divider
 *                  array(
 *                      'label' => 'DropdownB',
 *                      'content' => 'DropdownB, Anim pariatur cliche...',
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
	 *     // optional, an array of [[Dropdown]] widget items so to display a dropdown menu on the tab header. This
	 *     // attribute, apart from the original [[Dropdown::items]] settings, also has two extra special keys:
	 *     // - content: required, teh content (HTML) of teh tab the menu item is linked to
	 *     // - contentOptions: optional the HTML attributes of the tab content container
	 *     // note: if `dropdown` is set, then `content` will be ignored
	 *     // important: there is an issue with sub-dropdown menus, and as of 3.0, bootstrap won't support sub-dropdown
	 *     // @see https://github.com/twitter/bootstrap/issues/5050#issuecomment-11741727
	 *     'dropdown'=> array(...)
	 * )
	 * ```
	 */
	public $items = array();


	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$this->addCssClass($this->options, 'nav');
		$this->items = $this->normalizeItems();

	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('ul', $this->options) . "\n";
		echo $this->renderHeaders() . "\n";
		echo Html::endTag('ul');
		echo Html::beginTag('div', array('class' => 'tab-content')) . "\n";
		echo $this->renderContents() . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerPlugin('tab');
	}

	/**
	 * Renders tabs navigation.
	 * @return string the rendering result.
	 */
	protected function renderHeaders()
	{
		$headers = array();
		foreach ($this->items['headers'] as $item) {
			$options = ArrayHelper::getValue($item, 'options', array());
			if (isset($item['dropdown'])) {
				$headers[] = Html::tag(
					'li',
					Html::a($item['header'] . ' <b class="caret"></b>', "#", array(
						'class' => 'dropdown-toggle',
						'data-toggle' => 'dropdown'
					)) .
					Dropdown::widget(array('items' => $item['dropdown'], 'clientOptions' => false)),
					$options
				);
				continue;
			}
			$id = ArrayHelper::getValue($item, 'url');
			$headers[] = Html::tag('li', Html::a($item['header'], "{$id}", array('data-toggle' => 'tab')), $options);

		}
		return implode("\n", $headers);
	}

	/**
	 * Renders tabs contents.
	 * @return string the rendering result.
	 */
	protected function renderContents()
	{
		$contents = array();
		foreach ($this->items['contents'] as $item) {
			$options = ArrayHelper::getValue($item, 'options', array());
			$this->addCssClass($options, 'tab-pane');
			$contents[] = Html::tag('div', $item['content'], $options);

		}
		return implode("\n", $contents);
	}

	/**
	 * Normalizes the [[items]] property to divide headers from contents and to ease its rendering when there are
	 * headers with dropdown menus.
	 * @return array the normalized tabs items
	 * @throws InvalidConfigException
	 */
	protected function normalizeItems()
	{
		$items = array();
		$index = 0;
		foreach ($this->items as $item) {
			if (!isset($item['header'])) {
				throw new InvalidConfigException("The 'header' option is required.");
			}
			if (!isset($item['content']) && !isset($item['dropdown'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}
			$header = $content = array();
			$header['header'] = ArrayHelper::getValue($item, 'header');
			$header['options'] = ArrayHelper::getValue($item, 'headerOptions', array());
			if ($index === 0) {
				$this->addCssClass($header['options'], 'active');
			}
			if (isset($item['dropdown'])) {
				$this->addCssClass($header['options'], 'dropdown');

				$self = $this;
				$dropdown = function ($list) use (&$dropdown, &$items, &$index, $self) {
					$ddItems = $content = array();
					foreach ($list as $item) {
						if (is_string($item)) {
							$ddItems[] = $item;
							continue;
						}
						if (!isset($item['content']) && !isset($item['items'])) {
							throw new InvalidConfigException("The 'content' option is required.");
						}
						if (isset($item['items'])) {
							$item['items'] = $dropdown($item['items']);
						} else {
							$content['content'] = ArrayHelper::remove($item, 'content');
							$content['options'] = ArrayHelper::remove($item, 'contentOptions', array());
							if ($index === 0) {
								$self->addCssClass($content['options'], 'active');
								$self->addCssClass($item['options'], 'active');
							}
							$content['options']['id'] = ArrayHelper::getValue(
								$content['options'],
								'id',
								$self->options['id'] . '-tab' . $index++);
							$item['url'] = '#' . $content['options']['id'];
							$item['urlOptions']['data-toggle'] = 'tab';

							$items['contents'][] = $content;
						}
						$ddItems[] = $item;
					}
					return $ddItems;
				};
				$header['dropdown'] = $dropdown($item['dropdown']);

			} else {
				$content['content'] = ArrayHelper::getValue($item, 'content');
				$content['options'] = ArrayHelper::getValue($item, 'options', array());
				if ($index === 0) {
					$this->addCssClass($content['options'], 'active');
				}
				$content['options']['id'] = ArrayHelper::getValue(
					$content['options'],
					'id',
					$this->options['id'] . '-tab' . $index++);

				$header['url'] = "#" . ArrayHelper::getValue($content['options'], 'id');
				$items['contents'][] = $content;
			}
			$items['headers'][] = $header;
		}
		return $items;
	}
}