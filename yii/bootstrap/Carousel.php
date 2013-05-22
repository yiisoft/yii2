<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\base\ArrayHelper;
use yii\helpers\Html;

/**
 * Carousel renders a carousel bootstrap javascript component.
 *
 * For example,
 *
 * ```php
 * echo Carousel::widget(array(
 *     'items' => array(
 *         array(
 *             'src' => 'http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-01.jpg',
 *         ),
 *         array(
 *             'src' => 'http://twitter.github.io/bootstrap/assets/img/bootstrap-mdo-sfmoma-02.jpg',
 *             'captionLabel' => 'This is the caption label',
 *             'captionText' => 'This is the caption text'
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
	 * @var string the previous button label. Defaults to '&lsaquo;'.
	 */
	public $previousLabel = '&lsaquo;';
	/**
	 * @var string the next button label. Defaults to '&rsaquo;'.
	 */
	public $nextLabel = '&rsaquo;';
	/**
	 * @var boolean indicates whether the carousel should slide items.
	 */
	public $slide = true;
	/**
	 * @var boolean indicates whether to display the previous and next links.
	 */
	public $displayPreviousAndNext = true;
	/**
	 * @var array list of images to appear in the carousel. If this property is empty,
	 * the widget will not render anything. Each array element represents a single image in the carousel
	 * with the following structure:
	 *
	 * ```php
	 * array(
	 *     'src' => 'src of the image',  // required
	 *     'options' => ['html attributes of the item'], // optional
	 *     'imageOptions'=> ['html attributes of the image'] // optional
	 *     'captionLabel' => 'Title of the caption', // optional
	 *     'captionOptions' => ['html attributes of the caption'], // optional
	 *     'captionText' => 'Caption text, long description', // optional
	 *     'visible' => 'boolean', // optional, whether to display the item or not
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
		$this->getView()->registerAssetBundle('yii/bootstrap/carousel');
		$this->initOptions();
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
		$this->renderIndicators() . "\n";
		$this->renderItems() . "\n";
		$this->renderPreviousAndNext() . "\n";
		echo Html::endTag('div') . "\n";
	}

	/**
	 * Renders carousel indicators
	 */
	public function renderIndicators()
	{
		echo Html::beginTag('ol', array('class' => 'carousel-indicators')) . "\n";
		for ($i = 0, $ln = count($this->items); $i < $ln; $i++) {
			$options = array('data-target' => '#' . $this->options['id'], 'data-slide-to' => $i);
			if ($i === 0) {
				$this->addCssClass($options, 'active');
			}
			echo Html::tag('li', '', $options) . "\n";
		}
		echo Html::endTag('ol') . "\n";
	}

	/**
	 * Renders carousel items as specified on [[items]]
	 */
	public function renderItems()
	{
		echo Html::beginTag('div', array('class' => 'carousel-inner')) . "\n";
		for ($i = 0, $ln = count($this->items); $i < $ln; $i++) {
			echo $this->renderItem($this->items[$i], $i);
		}
		echo Html::endTag('div') . "\n";
	}

	/**
	 * Renders a single carousel item
	 * @param array $item a single item from [[items]]
	 * @param integer $index the item index as the first item should be set to `active`
	 */
	public function renderItem($item, $index)
	{
		$itemOptions = ArrayHelper::getValue($item, 'options', array());
		$this->addCssClass($itemOptions, 'item');
		if ($index === 0) {
			$this->addCssClass($itemOptions, 'active');
		}
		echo Html::beginTag('div', $itemOptions) . "\n";
		echo Html::img($item['src'], ArrayHelper::getValue($item, 'imageOptions', array())) . "\n";

		if (ArrayHelper::getValue($item, 'captionLabel')) {
			$this->renderCaptioN($item);
		}

		echo Html::endTag('div') . "\n";

	}

	/**
	 * Renders the caption of an item
	 * @param array $item a single item from [[items]]
	 */
	public function renderCaption($item)
	{
		$captionOptions = ArrayHelper::getValue($item, 'captionOptions', array());
		$this->addCssClass($captionOptions, 'carousel-caption');

		echo Html::beginTag('div', $captionOptions) . "\n";
		echo Html::tag('h4', ArrayHelper::getValue($item, 'captionLabel')) . "\n";
		echo Html::tag('p', ArrayHelper::getValue($item, 'captionText', '')) . "\n";
		echo Html::endTag('div');
	}

	/**
	 * Renders previous and next button if [[displayPreviousAndNext]] is set to `true`
	 */
	public function renderPreviousAndNext()
	{
		if (!$this->displayPreviousAndNext) {
			return;
		}
		echo Html::a($this->previousLabel, '#' . $this->options['id'], array(
				'class' => 'left carousel-control',
				'data-slide' => 'prev'
			)) .
			"\n" .
			Html::a($this->nextLabel, '#' . $this->options['id'], array(
				'class' => 'right carousel-control',
				'data-slide' => 'next'
			)) .
			"\n";
	}

	/**
	 * Initializes the widget options.
	 * This method sets the default values for various options.
	 */
	public function initOptions()
	{
		$this->addCssClass($this->options, 'carousel');
		if ($this->slide) {
			$this->addCssClass($this->options, 'slide');
		}
	}
}