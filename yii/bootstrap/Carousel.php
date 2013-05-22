<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\base\Model;
use yii\helpers\base\ArrayHelper;
use yii\helpers\Html;

/**
 * Carousel renders a carousel bootstrap javascript component.
 *
 * For example:
 *
 * ```php
 * echo Carousel::widget(array(
 *     'items' => array(
 *         '<img src="http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-01.jpg"/>',
 *         array(
 *             'content' => '<img src="http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-02.jpg"/>',
 *         ),
 *         array(
 *             'content' => '<img src="http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-03.jpg"/>',
 *             'caption' => '<h4>This is title</h5><p>This is the caption text</p>',
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
	 * @var array indicates what labels should be displayed on next and previous carousel controls. If [[controls]] is
	 * set to `false` the controls will not be displayed.
	 */
	public $controls = array('&lsaquo;', '&rsaquo;');
	/**
	 * @var array list of images to appear in the carousel. If this property is empty,
	 * the widget will not render anything. Each array element represents a single image in the carousel
	 * with the following structure:
	 *
	 * ```php
	 * array(
	 *     'content' => 'html, for example image',  // required
	 *     'caption'=> ['html attributes of the image'], // optional
	 *     'options' => ['html attributes of the item'], // optional
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
		if (empty($this->items)) {
			return;
		}

		echo Html::beginTag('div', $this->options) . "\n";
		echo $this->renderIndicators() . "\n";
		echo $this->renderItems() . "\n";
		echo $this->renderPreviousAndNext() . "\n";
		echo Html::endTag('div') . "\n";

		$this->registerPlugin('carousel');
	}

	/**
	 * Renders carousel indicators
	 */
	public function renderIndicators()
	{
		ob_start();
		echo Html::beginTag('ol', array('class' => 'carousel-indicators')) . "\n";
		for ($i = 0, $count = count($this->items); $i < $count; $i++) {
			$options = array('data-target' => '#' . $this->options['id'], 'data-slide-to' => $i);
			if ($i === 0) {
				$this->addCssClass($options, 'active');
			}
			echo Html::tag('li', '', $options) . "\n";
		}
		echo Html::endTag('ol') . "\n";
		return ob_get_clean();
	}

	/**
	 * Renders carousel items as specified on [[items]]
	 */
	public function renderItems()
	{
		ob_start();
		echo Html::beginTag('div', array('class' => 'carousel-inner')) . "\n";
		for ($i = 0, $count = count($this->items); $i < $count; $i++) {
			$this->renderItem($this->items[$i], $i);
		}
		echo Html::endTag('div') . "\n";
		return ob_get_clean();
	}

	/**
	 * Renders a single carousel item
	 * @param mixed $item a single item from [[items]]
	 * @param integer $index the item index as the first item should be set to `active`
	 */
	public function renderItem($item, $index)
	{
		if (is_string($item)) {
			$itemContent = $item;
			$itemCaption = null;
			$itemOptions = array();
		} else {
			$itemContent = $item['content']; // if not string, must be array, force required key
			$itemCaption = ArrayHelper::getValue($item, 'caption');
			$itemOptions = ArrayHelper::getValue($item, 'options', array());
		}

		$this->addCssClass($itemOptions, 'item');
		if ($index === 0) {
			$this->addCssClass($itemOptions, 'active');
		}

		echo Html::beginTag('div', $itemOptions) . "\n";
		echo $itemContent . "\n";
		if ($itemCaption !== null) {
			echo Html::tag('div', $itemCaption, array('class' => 'carousel-caption')) . "\n";
		}
		echo Html::endTag('div') . "\n";
	}

	/**
	 * Renders previous and next button if [[displayPreviousAndNext]] is set to `true`
	 */
	public function renderPreviousAndNext()
	{
		if ($this->controls === false || !(isset($this->controls[0], $this->controls[1]))) {
			return;
		}
		echo Html::a($this->controls[0], '#' . $this->options['id'], array(
				'class' => 'left carousel-control',
				'data-slide' => 'prev',
			)) . "\n"
			. Html::a($this->controls[1], '#' . $this->options['id'], array(
				'class' => 'right carousel-control',
				'data-slide' => 'next',
			));
	}
}
