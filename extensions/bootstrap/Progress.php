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
 * echo Progress::widget([
 *     'percent' => 60,
 *     'label' => 'test',
 * ]);
 *
 * // styled
 * echo Progress::widget([
 *     'percent' => 65,
 *     'barOptions' => ['class' => 'progress-bar-danger']
 * ]);
 *
 * // striped
 * echo Progress::widget([
 *     'percent' => 70,
 *     'barOptions' => ['class' => 'progress-bar-warning'],
 *     'options' => ['class' => 'progress-striped']
 * ]);
 *
 * // striped animated
 * echo Progress::widget([
 *     'percent' => 70,
 *     'barOptions' => ['class' => 'progress-bar-success'],
 *     'options' => ['class' => 'active progress-striped']
 * ]);
 *
 * // stacked bars
 * echo Progress::widget([
 *     'bars' => [
 *         ['percent' => 30, 'options' => ['class' => 'progress-bar-danger']],
 *         ['percent' => 30, 'label' => 'test', 'options' => ['class' => 'progress-bar-success']],
 *         ['percent' => 35, 'options' => ['class' => 'progress-bar-warning']],
 *     ]
 * ]);
 * ```
 * @see http://getbootstrap.com/components/#progress
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Progress extends Widget
{
    /**
     * @var string the button label.
     */
    public $label;
    /**
     * @var integer the amount of progress as a percentage.
     */
    public $percent = 0;
    /**
     * @var array the HTML attributes of the bar.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $barOptions = [];
    /**
     * @var array a set of bars that are stacked together to form a single progress bar.
     * Each bar is an array of the following structure:
     *
     * ```php
     * [
     *     // required, the amount of progress as a percentage.
     *     'percent' => 30,
     *     // optional, the label to be displayed on the bar
     *     'label' => '30%',
     *     // optional, array, additional HTML attributes for the bar tag
     *     'options' => [],
     * ]
     * ```
     */
    public $bars;

    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, 'progress');
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo Html::beginTag('div', $this->options) . "\n";
        echo $this->renderProgress() . "\n";
        echo Html::endTag('div') . "\n";
        BootstrapAsset::register($this->getView());
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
        $bars = [];
        foreach ($this->bars as $bar) {
            $label = ArrayHelper::getValue($bar, 'label', '');
            if (!isset($bar['percent'])) {
                throw new InvalidConfigException("The 'percent' option is required.");
            }
            $options = ArrayHelper::getValue($bar, 'options', []);
            $bars[] = $this->renderBar($bar['percent'], $label, $options);
        }

        return implode("\n", $bars);
    }

    /**
     * Generates a bar
     * @param integer $percent the percentage of the bar
     * @param string $label, optional, the label to display at the bar
     * @param array $options the HTML attributes of the bar
     * @return string the rendering result.
     */
    protected function renderBar($percent, $label = '', $options = [])
    {
        $defaultOptions = [
            'role' => 'progressbar',
            'aria-valuenow' => $percent,
            'aria-valuemin' => 0,
            'aria-valuemax' => 100,
            'style' => "width:{$percent}%",
        ];
        $options = array_merge($defaultOptions, $options);
        Html::addCssClass($options, 'progress-bar');

        $out = Html::beginTag('div', $options);
        $out .= $label;
        $out .= Html::tag('span', \Yii::t('yii', '{percent}% Complete', ['percent' => $percent]), [
            'class' => 'sr-only'
        ]);
        $out .= Html::endTag('div');

        return $out;
    }
}
