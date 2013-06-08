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
 * Progress renders a bootstrap progress bar component.
 *
 * For example,
 *
 * ```php
 * // default with label
 * echo Progress::widget(array(
 *     'percent' => 60,
 *     'label' => 'test',
 * ));
 *
 * // styled
 * echo Progress::widget(array(
 *     'percent' => 65,
 *     'barOptions' => array('class' => 'bar-danger')
 * ));
 *
 * // striped
 * echo Progress::widget(array(
 *     'percent' => 70,
 *     'barOptions' => array('class' => 'bar-warning'),
 *     'options' => array('class' => 'progress-striped')
 * ));
 *
 * // striped animated
 * echo Progress::widget(array(
 *     'percent' => 70,
 *     'barOptions' => array('class' => 'bar-success'),
 *     'options' => array('class' => 'active progress-striped')
 * ));
 *
 * // stacked bars
 * echo Progress::widget(array(
 *     'bars' => array(
 *         array('percent' => 30, 'options' => array('class' => 'bar-danger')),
 *         array('percent' => 30, 'label'=>'test', 'options' => array('class' => 'bar-success')),
 *         array('percent' => 35, 'options' => array('class' => 'bar-warning'))
 *     )
 * ));
 * ```
 * @see http://twitter.github.io/bootstrap/components.html#progress
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Progress extends Widget
{
	/**
	 * @var string the button label
	 */
	public $label;
	/**
	 * @var integer the amount of progress as a percentage.
	 */
	public $percent = 0;
	/**
	 * @var array the HTML attributes of the
	 */
	public $barOptions = array();
	/**
	 * @var array a set of bars that are stacked together to form a single progress bar.
	 * Each bar is an array of the following structure:
	 *
	 * ```php
	 * array(
	 *     // required, the amount of progress as a percentage.
	 *     'percent' => 30,
	 *     // optional, the label to be displayed on the bar
	 *     'label' => '30%',
	 *     // optional, array, additional HTML attributes for the bar tag
	 *     'options' => array(),
	 * )
	 */
	public $bars;


	/**
	 * Initializes the widget.
	 * If you override this method, make sure you call the parent implementation first.
	 */
	public function init()
	{
		parent::init();
		$this->addCssClass($this->options, 'progress');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('div', $this->options) . "\n";
		echo $this->renderProgress() . "\n";
		echo Html::endTag('div') . "\n";
		$this->getView()->registerAssetBundle(static::$responsive ? 'yii/bootstrap/responsive' : 'yii/bootstrap');
	}

	/**
	 * Renders the progress.
	 * @return string the rendering result.
	 * @throws InvalidConfigException if the "percent" option is not set in a stacked progress bar.
	 */
	protected function renderProgress()
	{
		if (empty($this->bars)) {
			return $this->renderBar($this->percent, $this->label, $this->barOptions);
		}
		$bars = array();
		foreach ($this->bars as $bar) {
			$label = ArrayHelper::getValue($bar, 'label', '');
			if (!isset($bar['percent'])) {
				throw new InvalidConfigException("The 'percent' option is required.");
			}
			$options = ArrayHelper::getValue($bar, 'options', array());
			$bars[] = $this->renderBar($bar['percent'], $label, $options);
		}
		return implode("\n", $bars);
	}

	/**
	 * Generates a bar
	 * @param int $percent the percentage of the bar
	 * @param string $label, optional, the label to display at the bar
	 * @param array $options the HTML attributes of the bar
	 * @return string the rendering result.
	 */
	protected function renderBar($percent, $label = '', $options = array())
	{
		$this->addCssClass($options, 'bar');
		$options['style'] = "width:{$percent}%";
		return Html::tag('div', $label, $options);
	}
}
