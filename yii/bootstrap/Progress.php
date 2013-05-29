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
 * // stacked and one with label
 * echo Progress::widget(array(
 *     'stacked' => array(
 *         array('percent' => 30, 'options' => array('class' => 'bar-danger')),
 *         array('label'=>'test', 'percent' => 30, 'options' => array('class' => 'bar-success')),
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
	 * @var array $stacked set to an array of progress bar values to display stacked progress bars
	 *
	 * ```php
	 *  'stacked'=>array(
	 *      array('percent'=>'30', 'options'=>array('class'=>'custom')),
	 *      array('percent'=>'30'),
	 *  )
	 * ```
	 */
	public $stacked = false;


	/**
	 * Initializes the widget.
	 * If you override this method, make sure you call the parent implementation first.
	 */
	public function init()
	{
		if ($this->label === null && $this->stacked == false) {
			throw new InvalidConfigException("The 'percent' option is required.");
		}
		parent::init();
		$this->addCssClass($this->options, 'progress');
		$this->getView()->registerAssetBundle(static::$responsive ? 'yii/bootstrap/responsive' : 'yii/bootstrap');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('div', $this->options) . "\n";
		echo $this->renderProgress() . "\n";
		echo Html::endTag('div') . "\n";
	}

	/**
	 * Generates a bar
	 * @param int $percent the percentage of the bar
	 * @param string $label, optional, the label to display at the bar
	 * @param array $options the HTML attributes of the bar
	 * @return string the rendering result.
	 */
	public function bar($percent, $label = '', $options = array())
	{
		$this->addCssClass($options, 'bar');
		$options['style'] = "width:{$percent}%";
		return Html::tag('div', $label, $options);
	}

	/**
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 */
	protected function renderProgress()
	{
		if ($this->stacked === false) {
			return $this->bar($this->percent, $this->label, $this->barOptions);
		}
		$bars = array();
		foreach ($this->stacked as $item) {
			$label = ArrayHelper::getValue($item, 'label', '');
			if (!isset($item['percent'])) {
				throw new InvalidConfigException("The 'percent' option is required.");
			}
			$options = ArrayHelper::getValue($item, 'options', array());

			$bars[] = $this->bar($item['percent'], $label, $options);
		}
		return implode("\n", $bars);
	}

}