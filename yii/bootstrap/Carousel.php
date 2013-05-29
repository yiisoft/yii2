<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Carousel renders a carousel bootstrap javascript component.
 *
 * For example:
 *
 * ```php
 * echo Carousel::widget(array(
 *     'items' => array(
 *         // the item contains only the image
 *         '<img src="http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-01.jpg"/>',
 *         // equivalent to the above
 *         array(
 *             'content' => '<img src="http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-02.jpg"/>',
 *         ),
 *         // the item contains both the image and the caption
 *         array(
 *             'content' => '<img src="http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-03.jpg"/>',
 *             'caption' => '<h4>This is title</h4><p>This is the caption text</p>',
 *             'options' => array(...),
 *         ),
 *     )
 * ));
 * ```
 *
 * @see http://twitter.github.io/bootstrap/javascript.html#carousel
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Carousel extends Widget
{
	/**
	 * @var array|boolean the labels for the previous and the next control buttons.
	 * If false, it means the previous and the next control buttons should not be displayed.
	 */
	public $controls = array('&lsaquo;', '&rsaquo;');
	/**
	 * @var array list of slides in the carousel. Each array element represents a single
	 * slide with the following structure:
	 *
	 * ```php
	 * array(
	 *     // required, slide content (HTML), such as an image tag
	 *     'content' => '<img src="http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-01.jpg"/>',
	 *     // optional, the caption (HTML) of the slide
	 *     'caption'=> '<h4>This is title</h4><p>This is the caption text</p>',
	 *     // optional the HTML attributes of the slide container
	 *     'options' => array(),
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
		$this->addCssClass($this->options, 'carousel');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('div', $this->options) . "\n";
		echo $this->renderIndicators() . "\n";
		echo $this->renderItems() . "\n";
		echo $this->renderControls() . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerPlugin('carousel');
	}

	/**
	 * Renders carousel indicators.
	 * @return string the rendering result
	 */
	public function renderIndicators()
	{
		$indicators = array();
		for ($i = 0, $count = count($this->items); $i < $count; $i++) {
			$options = array('data-target' => '#' . $this->options['id'], 'data-slide-to' => $i);
			if ($i === 0) {
				$this->addCssClass($options, 'active');
			}
			$indicators[] = Html::tag('li', '', $options);
		}
		return Html::tag('ol', implode("\n", $indicators), array('class' => 'carousel-indicators'));
	}

	/**
	 * Renders carousel items as specified on [[items]].
	 * @return string the rendering result
	 */
	public function renderItems()
	{
		$items = array();
		for ($i = 0, $count = count($this->items); $i < $count; $i++) {
			$items[] = $this->renderItem($this->items[$i], $i);
		}
		return Html::tag('div', implode("\n", $items), array('class' => 'carousel-inner'));
	}

	/**
	 * Renders a single carousel item
	 * @param string|array $item a single item from [[items]]
	 * @param integer $index the item index as the first item should be set to `active`
	 * @return string the rendering result
	 * @throws InvalidConfigException if the item is invalid
	 */
	public function renderItem($item, $index)
	{
		if (is_string($item)) {
			$content = $item;
			$caption = null;
			$options = array();
		} elseif (isset($item['content'])) {
			$content = $item['content'];
			$caption = ArrayHelper::getValue($item, 'caption');
			if ($caption !== null) {
				$caption = Html::tag('div', $caption, array('class' => 'carousel-caption'));
			}
			$options = ArrayHelper::getValue($item, 'options', array());
		} else {
			throw new InvalidConfigException('The "content" option is required.');
		}

		$this->addCssClass($options, 'item');
		if ($index === 0) {
			$this->addCssClass($options, 'active');
		}

		return Html::tag('div', $content . "\n" . $caption, $options);
	}

	/**
	 * Renders previous and next control buttons.
	 * @throws InvalidConfigException if [[controls]] is invalid.
	 */
	public function renderControls()
	{
		if (isset($this->controls[0], $this->controls[1])) {
			return Html::a($this->controls[0], '#' . $this->options['id'], array(
				'class' => 'left carousel-control',
				'data-slide' => 'prev',
			)) . "\n"
			. Html::a($this->controls[1], '#' . $this->options['id'], array(
				'class' => 'right carousel-control',
				'data-slide' => 'next',
			));
		} elseif ($this->controls === false) {
			return '';
		} else {
			throw new InvalidConfigException('The "controls" property must be either false or an array of two elements.');
		}
	}
}
