<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * A Bootstrap 3 enhanced version of [[yii\widgets\ActiveField]].
 *
 * This class adds some useful features to [[yii\widgets\ActiveField|ActiveField]] to render all
 * sorts of Bootstrap 3 form fields in different form layouts:
 *
 * - [[inputTemplate]] is an optional template to render complex inputs, for example input groups
 * - [[horizontalClass]] defines the CSS grid classes to add to label, wrapper, error and hint
 *   in horizontal forms
 * - [[inline]]/[[inline()]] is used to render inline [[checkboxList()]] and [[radioList()]]
 * - [[enableError]] can be set to `false` to disable to the error
 * - [[enableLabel]] can be set to `false` to disable to the label
 * - [[label()]] can be used with a `boolean` argument to enable/disable the label
 *
 * There are also some new placeholders that you can use in the [[template]] configuration:
 *
 * - `{beginLabel}`: the opening label tag
 * - `{labelTitle}`: the label title for use with `{beginLabel}`/`{endLabel}`
 * - `{endLabel}`: the closing label tag
 * - `{beginWrapper}`: the opening wrapper tag
 * - `{endWrapper}`: the closing wrapper tag
 *
 * The wrapper tag is only used for some layouts and form elements.
 *
 * Note that some elements use slightly different defaults for [[template]] and other options.
 * In particular the elements are [[checkbox()]], [[checkboxList()]] and [[radioList()]].
 * So to further customize these elements you may want to pass your custom options.
 *
 * Example:
 *
 * ```php
 * use yii\bootstrap\ActiveForm;
 *
 * $form = ActiveForm::begin(['layout' => 'horizontal'])
 *
 * // Form field without label
 * echo $form->field($model, 'demo', [
 *     'inputOptions' => [
 *         'placeholder' => $model->getAttributeLabel('demo'),
 *     ],
 * ])->label(false);
 *
 * // Inline radio list
 * echo $form->field($model, 'demo')->inline()->radioList($items);
 *
 * // Control sizing in horizontal mode
 * echo $form->field($model, 'demo', [
 *     'horizontalCssClasses' => [
 *         'wrapper' => 'col-sm-2',
 *     ]
 * ]);
 *
 * // With standard layout you would use 'template' to size a specific field:
 * // echo $form->field($model, 'demo', [
 * //     'template' => '{label} <div class="row"><div class="col-sm-4">{input}{error}{hint}</div></div>'
 * // ]);
 *
 *  // Input group
 *  echo $form->field($model, 'demo', [
 *      'inputTemplate' => '<div class="input-group"><span class="input-group-addon">@</span>{input}</div>',
 *  ]);
 *
 *  ActiveForm::end();
 *  ```
 *
 * @see \yii\bootstrap\ActiveForm
 * @see http://getbootstrap.com/css/#forms
 *
 * @author Michael Härtl <haertl.mike@gmail.com>
 * @since 2.0
 */
class ActiveField extends \yii\widgets\ActiveField
{
    /**
     * @var bool whether to render [[checkboxList()]] and [[radioList()]] inline. Default is `false`.
     */
    public $inline = false;

    /**
     * @var string|null optional template to render the `{input}` placheolder content
     */
    public $inputTemplate;

    /**
     * @var array options for the wrapper tag, used in the `{beginWrapper}` placeholder
     */
    public $wrapperOptions = [];

    /**
     * @var null|array CSS grid classes for horizontal layout. This must be an array with these keys:
     *  - 'offset' the offset grid class to append to the wrapper if no label is rendered
     *  - 'label' the label grid class
     *  - 'wrapper' the wrapper grid class
     *  - 'error' the error grid class
     *  - 'hint' the hint grid class
     */
    public $horizontalCssClasses;

    /**
     * @var bool whether to render the error. Default is `true` except for layout `inline`.
     */
    public $enableError = true;

    /**
     * @var bool whether to render the label. Default is `true`.
     */
    public $enableLabel = true;

    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        $layoutConfig = $this->createLayoutConfig($config);
        $config = ArrayHelper::merge($layoutConfig, $config);
        return parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function render($content = null)
    {
        if ($content === null) {
            if (!isset($this->parts['{beginWrapper}'])) {
                $options = $this->wrapperOptions;
                $tag = ArrayHelper::remove($options, 'tag', 'div');
                $this->parts['{beginWrapper}'] = Html::beginTag($tag, $options);
                $this->parts['{endWrapper}'] = Html::endTag($tag);
            }
            if ($this->enableLabel===false) {
                $this->parts['{label}'] = '';
                $this->parts['{beginLabel}'] = '';
                $this->parts['{labelTitle}'] = '';
                $this->parts['{endLabel}'] = '';
            } elseif (!isset($this->parts['{beginLabel}'])) {
                $this->renderLabelParts();
            }
            if ($this->enableError===false) {
                $this->parts['{error}'] = '';
            }
            if ($this->inputTemplate) {
                $input = isset($this->parts['{input}']) ?
                    $this->parts['{input}'] : Html::activeTextInput($this->model, $this->attribute, $this->inputOptions);
                $this->parts['{input}'] = strtr($this->inputTemplate, ['{input}' => $input]);
            }
        }
        return parent::render($content);
    }

    /**
     * @inheritDoc
     */
    public function checkbox($options = [], $enclosedByLabel = true)
    {
        if ($enclosedByLabel) {
            if (!isset($options['template'])) {
                if ($this->form->layout==='horizontal') {
                    $this->template = "{beginWrapper}\n<div class=\"checkbox\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n</div>\n{error}\n{endWrapper}\n{hint}";
                    Html::addCssClass($this->wrapperOptions, $this->horizontalCssClasses['offset']);
                } else {
                    $this->template = "<div class=\"checkbox\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>";
                }
            }
            $this->labelOptions['class'] = null;
        }

        parent::checkbox($options, false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function checkboxList($items, $options = [])
    {
        if ($this->inline) {
            if (!isset($options['template'])) {
                $this->template = "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}";
            }
            if (!isset($options['itemOptions'])) {
                $options['itemOptions'] = [
                    'container' => false,
                    'labelOptions' => ['class' => 'checkbox-inline'],
                ];
            }
        }
        parent::checkboxList($items, $options);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function radioList($items, $options = [])
    {
        if ($this->inline) {
            if (!isset($options['template'])) {
                $this->template = "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}";
            }
            if (!isset($options['itemOptions'])) {
                $options['itemOptions'] = [
                    'container' => false,
                    'labelOptions' => ['class' => 'radio-inline'],
                ];
            }
        }
        parent::radioList($items, $options);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function label($label = null, $options = [])
    {
        if (is_bool($label)) {
            $this->enableLabel = $label;
            if ($label===false && $this->form->layout==='horizontal') {
                Html::addCssClass($this->wrapperOptions, $this->horizontalCssClasses['offset']);
            }
        } else {
            $this->renderLabelParts($label, $options);
            parent::label($label, $options);
        }
        return $this;
    }

    /**
     * @param bool $value whether to render a inline list
     * @return static the field object itself
     * Make sure you call this method before [[checkboxList()]] or [[radioList()]] to have any effect.
     */
    public function inline($value = true)
    {
        $this->inline = (bool)$value;
        return $this;
    }

    /**
     * @param array $instanceConfig the configuration passed to this instance's constructor
     * @return array the layout specific default configuration for this instance
     */
    protected function createLayoutConfig($instanceConfig)
    {
        $config = [
            'hintOptions' => [
                'tag' => 'p',
                'class' => 'help-block',
            ],
            'errorOptions' => [
                'tag' => 'p',
                'class' => 'help-block',
            ],
            'inputOptions' => [
                'class' => 'form-control',
            ],
        ];

        $layout = $instanceConfig['form']->layout;

        if ($layout==='horizontal') {
            $config['template'] = "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}";
            $cssClasses = [
                'offset' => 'col-sm-offset-3',
                'label' => 'col-sm-3',
                'wrapper' => 'col-sm-6',
                'error' => '',
                'hint' => 'col-sm-3',
            ];
            if (isset($instanceConfig['horizontalCssClasses'])) {
                $cssClasses = ArrayHelper::merge($cssClasses, $instanceConfig['horizontalCssClasses']);
            }
            $config['horizontalCssClasses'] = $cssClasses;
            $config['wrapperOptions'] = ['class' => $cssClasses['wrapper']];
            $config['labelOptions'] = ['class' => 'control-label '.$cssClasses['label']];
            $config['errorOptions'] = ['class' => 'help-block '.$cssClasses['error']];
            $config['hintOptions'] = ['class' => 'help-block '.$cssClasses['hint'] ];
        } elseif ($layout==='inline') {
            $config['labelOptions'] = ['class' => 'sr-only'];
            $config['enableError'] = false;
        }

        return $config;
    }

    /**
     * @param string|null $label the label or null to use model label
     * @param array $options the tag options
     */
    protected function renderLabelParts($label = null, $options = [])
    {
        $options = array_merge($this->labelOptions, $options);
        if ($label===null) {
            if (isset($options['label'])) {
                $label = $options['label'];
                unset($options['label']);
            } else {
                $attribute = Html::getAttributeName($this->attribute);
                $label = $this->model->getAttributeLabel($attribute);
            }
        }
        $this->parts['{beginLabel}'] = Html::beginTag('label', $options);
        $this->parts['{endLabel}'] = Html::endTag('label');
        $this->parts['{labelTitle}'] = Html::encode($label);
    }
}
