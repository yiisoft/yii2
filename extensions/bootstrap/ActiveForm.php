<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\helpers\Html;
use yii\base\InvalidConfigException;

/**
 * A Bootstrap 3 enhanced version of [[\yii\widgets\ActiveForm]].
 *
 * This class mainly adds the [[layout]] property to choose a Bootstrap 3 form layout.
 * So for example to render a horizontal form you would:
 *
 * ```php
 * use yii\bootstrap\ActiveForm;
 *
 * $form = ActiveForm::begin(['layout' => 'horizontal'])
 * ```
 *
 * This will set default values for the [[yii\bootstrap\ActiveField|ActiveField]]
 * to render horizontal form fields. In particular the [[yii\bootstrap\ActiveField::template|template]]
 * is set to `{label} {beginWrapper} {input} {error} {endWrapper} {hint}` and the
 * [[yii\bootstrap\ActiveField::horizontalCssClasses|horizontalCssClasses]] are set to:
 *
 * ```php
 * [
 *     'offset' => 'col-sm-offset-3',
 *     'label' => 'col-sm-3',
 *     'wrapper' => 'col-sm-6',
 *     'error' => '',
 *     'hint' => 'col-sm-3',
 * ]
 * ```
 *
 * To get a different column layout in horizontal mode you can modify those options
 * through [[fieldConfig]]:
 *
 * ```php
 * $form = ActiveForm::begin([
 *     'layout' => 'horizontal',
 *     'fieldConfig' => [
 *         'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
 *         'horizontalCssClasses' => [
 *             'label' => 'col-sm-4',
 *             'offset' => 'col-sm-offset-4',
 *             'wrapper' => 'col-sm-8',
 *             'error' => '',
 *             'hint' => '',
 *         ],
 *     ],
 * ]);
 * ```
 *
 * @see \yii\bootstrap\ActiveField for details on the [[fieldConfig]] options
 * @see http://getbootstrap.com/css/#forms
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @since 2.0
 */
class ActiveForm extends \yii\widgets\ActiveForm
{
    /**
     * @var array HTML attributes for the form tag. Default is `['role' => 'form']`.
     */
    public $options = ['role' => 'form'];
    /**
     * @var string the form layout. Either 'default', 'horizontal' or 'inline'.
     * By choosing a layout, an appropriate default field configuration is applied. This will
     * render the form fields with slightly different markup for each layout. You can
     * override these defaults through [[fieldConfig]].
     * @see \yii\bootstrap\ActiveField for details on Bootstrap 3 field configuration
     */
    public $layout = 'default';


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!in_array($this->layout, ['default', 'horizontal', 'inline'])) {
            throw new InvalidConfigException('Invalid layout type: ' . $this->layout);
        }

        if ($this->layout !== 'default') {
            Html::addCssClass($this->options, 'form-' . $this->layout);
        }
        if (!isset($this->fieldConfig['class'])) {
            $this->fieldConfig['class'] = ActiveField::className();
        }
        parent::init();
    }
}
