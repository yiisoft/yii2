<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use Yii;
use yii\base\InvalidParamException;
use yii\helpers\FormatConverter;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * DatePicker renders a `datepicker` jQuery UI widget.
 *
 * For example to use the datepicker with a [[yii\base\Model|model]]:
 *
 * ```php
 * echo DatePicker::widget([
 *     'model' => $model,
 *     'attribute' => 'from_date',
 *     //'language' => 'ru',
 *     //'dateFormat' => 'yyyy-MM-dd',
 * ]);
 * ```
 *
 * The following example will use the name property instead:
 *
 * ```php
 * echo DatePicker::widget([
 *     'name'  => 'from_date',
 *     'value'  => $value,
 *     //'language' => 'ru',
 *     //'dateFormat' => 'yyyy-MM-dd',
 * ]);
 * ```
 *
 * Note that empty values like empty strings and 0 will result in a date displayed as `1970-01-01`.
 * So to make sure empty values result in an empty text field in the datepicker you need to add a
 * validation filter in your model that sets the value to `null` in case when no date has been entered:
 *
 * ```php
 * [['from_date'], 'default', 'value' => null],
 * ```
 *
 * @see http://api.jqueryui.com/datepicker/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class DatePicker extends InputWidget
{
    /**
     * @var string the locale ID (eg 'fr', 'de') for the language to be used by the date picker.
     * If this property is empty, then the current application language will be used.
     */
    public $language;
    /**
     * @var boolean If true, shows the widget as an inline calendar and the input as a hidden field.
     */
    public $inline = false;
    /**
     * @var array the HTML attributes for the container tag. This is only used when [[inline]] is true.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $containerOptions = [];
    /**
     * @var string the format string to be used for formatting the date value. This option will be used
     * to populate the [[clientOptions|clientOption]] `dateFormat`.
     * The value can be one of "short", "medium", "long", or "full", which represents a preset format of different lengths.
     *
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/de/function.date.php)-function.
     *
     * For example:
     *
     * ```php
     * 'MM/dd/yyyy' // date in ICU format
     * 'php:m/d/Y' // the same date in PHP format
     * ```
     *
     * If not set the default value will be taken from `Yii::$app->formatter->dateFormat`.
     */
    public $dateFormat;
    /**
     * @var string the model attribute that this widget is associated with.
     * The value of the attribute will be converted using [[\yii\i18n\Formatter::asDate()|`Yii::$app->formatter->asDate()`]]
     * with the [[dateFormat]] if it is not null.
     */
    public $attribute;
    /**
     * @var string the input value.
     * This value will be converted using [[\yii\i18n\Formatter::asDate()|`Yii::$app->formatter->asDate()`]]
     * with the [[dateFormat]] if it is not null.
     */
    public $value;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->inline && !isset($this->containerOptions['id'])) {
            $this->containerOptions['id'] = $this->options['id'] . '-container';
        }
        if ($this->dateFormat === null) {
            $this->dateFormat = Yii::$app->formatter->dateFormat;
        }
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo $this->renderWidget() . "\n";

        $containerID = $this->inline ? $this->containerOptions['id'] : $this->options['id'];
        $language = $this->language ? $this->language : Yii::$app->language;

        if (strncmp($this->dateFormat, 'php:', 4) === 0) {
            $this->clientOptions['dateFormat'] = FormatConverter::convertDatePhpToJui(substr($this->dateFormat, 4), 'date', $language);
        } else {
            $this->clientOptions['dateFormat'] = FormatConverter::convertDateIcuToJui($this->dateFormat, 'date', $language);
        }

        if ($language != 'en-US') {
            $view = $this->getView();
            $bundle = DatePickerLanguageAsset::register($view);
            if ($bundle->autoGenerate) {
                $view->registerJsFile($bundle->baseUrl . "/ui/i18n/datepicker-$language.js", [
                    'depends' => [JuiAsset::className()],
                ]);
            }
            $options = Json::encode($this->clientOptions);
            $view->registerJs("$('#{$containerID}').datepicker($.extend({}, $.datepicker.regional['{$language}'], $options));");
        } else {
            $this->registerClientOptions('datepicker', $containerID);
        }

        $this->registerClientEvents('datepicker', $containerID);
        JuiAsset::register($this->getView());
    }

    /**
     * Renders the DatePicker widget.
     * @return string the rendering result.
     */
    protected function renderWidget()
    {
        $contents = [];

        // get formatted date value
        if ($this->hasModel()) {
            $value = Html::getAttributeValue($this->model, $this->attribute);
        } else {
            $value = $this->value;
        }
        if ($value !== null) {
            // format value according to dateFormat
            try {
                $value = Yii::$app->formatter->asDate($value, $this->dateFormat);
            } catch(InvalidParamException $e) {
                // ignore exception and keep original value if it is not a valid date
            }
        }
        $options = $this->options;
        $options['value'] = $value;

        if ($this->inline === false) {
            // render a text input
            if ($this->hasModel()) {
                $contents[] = Html::activeTextInput($this->model, $this->attribute, $options);
            } else {
                $contents[] = Html::textInput($this->name, $value, $options);
            }
        } else {
            // render an inline date picker with hidden input
            if ($this->hasModel()) {
                $contents[] = Html::activeHiddenInput($this->model, $this->attribute, $options);
            } else {
                $contents[] = Html::hiddenInput($this->name, $value, $options);
            }
            $this->clientOptions['defaultDate'] = $value;
            $this->clientOptions['altField'] = '#' . $this->options['id'];
            $contents[] = Html::tag('div', null, $this->containerOptions);
        }

        return implode("\n", $contents);
    }
}
