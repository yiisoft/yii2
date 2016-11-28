<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;

/**
 * MaskedInput generates a masked text input.
 *
 * MaskedInput is similar to [[Html::textInput()]] except that an input mask will be used to force users to enter
 * properly formatted data, such as phone numbers, social security numbers.
 *
 * To use MaskedInput, you must set the [[mask]] property. The following example
 * shows how to use MaskedInput to collect phone numbers:
 *
 * ```php
 * echo MaskedInput::widget([
 *     'name' => 'phone',
 *     'mask' => '999-999-9999',
 * ]);
 * ```
 *
 * You can also use this widget in an [[ActiveForm]] using the [[ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'from_date')->widget(\yii\widgets\MaskedInput::className(), [
 *     'mask' => '999-999-9999',
 * ]) ?>
 * ```
 *
 * The masked text field is implemented based on the
 * [jQuery input masked plugin](https://github.com/RobinHerbots/jquery.inputmask).
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 2.0
 */
class MaskedInput extends InputWidget
{
    /**
     * The name of the jQuery plugin to use for this widget.
     */
    const PLUGIN_NAME = 'inputmask';

    /**
     * @var string|array|JsExpression the input mask (e.g. '99/99/9999' for date input). The following characters
     * can be used in the mask and are predefined:
     *
     * - `a`: represents an alpha character (A-Z, a-z)
     * - `9`: represents a numeric character (0-9)
     * - `*`: represents an alphanumeric character (A-Z, a-z, 0-9)
     * - `[` and `]`: anything entered between the square brackets is considered optional user input. This is
     *   based on the `optionalmarker` setting in [[clientOptions]].
     *
     * Additional definitions can be set through the [[definitions]] property.
     */
    public $mask;
    /**
     * @var array custom mask definitions to use. Should be configured as `maskSymbol => settings`, where
     *
     * - `maskSymbol` is a string, containing a character to identify your mask definition and
     * - `settings` is an array, consisting of the following entries:
     *   - `validator`: string, a JS regular expression or a JS function.
     *   - `cardinality`: int, specifies how many characters are represented and validated for the definition.
     *   - `prevalidator`: array, validate the characters before the definition cardinality is reached.
     *   - `definitionSymbol`: string, allows shifting values from other definitions, with this `definitionSymbol`.
     */
    public $definitions;
    /**
     * @var array custom aliases to use. Should be configured as `maskAlias => settings`, where
     *
     * - `maskAlias` is a string containing a text to identify your mask alias definition (e.g. 'phone') and
     * - `settings` is an array containing settings for the mask symbol, exactly similar to parameters as passed in [[clientOptions]].
     */
    public $aliases;
    /**
     * @var array the JQuery plugin options for the input mask plugin.
     * @see https://github.com/RobinHerbots/jquery.inputmask
     */
    public $clientOptions = [];
    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'form-control'];
    /**
     * @var string the type of the input tag. Currently only 'text' and 'tel' are supported.
     * @see https://github.com/RobinHerbots/jquery.inputmask
     * @since 2.0.6
     */
    public $type = 'text';

    /**
     * @var string the hashed variable to store the pluginOptions
     */
    protected $_hashVar;


    /**
     * Initializes the widget.
     *
     * @throws InvalidConfigException if the "mask" property is not set.
     */
    public function init()
    {
        parent::init();
        if (empty($this->mask) && empty($this->clientOptions['alias'])) {
            throw new InvalidConfigException("Either the 'mask' property or the 'clientOptions[\"alias\"]' property must be set.");
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            echo Html::activeInput($this->type, $this->model, $this->attribute, $this->options);
        } else {
            echo Html::input($this->type, $this->name, $this->value, $this->options);
        }
    }

    /**
     * Generates a hashed variable to store the plugin `clientOptions`. Helps in reusing the variable for similar
     * options passed for other widgets on the same page. The following special data attribute will also be
     * added to the input field to allow accessing the client options via javascript:
     *
     * - 'data-plugin-inputmask' will store the hashed variable storing the plugin options.
     *
     * @param View $view the view instance
     * @author [Thiago Talma](https://github.com/thiagotalma)
     */
    protected function hashPluginOptions($view)
    {
        $encOptions = empty($this->clientOptions) ? '{}' : Json::htmlEncode($this->clientOptions);
        $this->_hashVar = self::PLUGIN_NAME . '_' . hash('crc32', $encOptions);
        $this->options['data-plugin-' . self::PLUGIN_NAME] = $this->_hashVar;
        $view->registerJs("var {$this->_hashVar} = {$encOptions};", View::POS_READY);
    }

    /**
     * Initializes client options
     */
    protected function initClientOptions()
    {
        $options = $this->clientOptions;
        foreach ($options as $key => $value) {
            if (!$value instanceof JsExpression && in_array($key, ['oncomplete', 'onincomplete', 'oncleared', 'onKeyUp',
                    'onKeyDown', 'onBeforeMask', 'onBeforePaste', 'onUnMask', 'isComplete', 'determineActiveMasksetIndex'], true)
            ) {
                $options[$key] = new JsExpression($value);
            }
        }
        $this->clientOptions = $options;
    }

    /**
     * Registers the needed client script and options.
     */
    public function registerClientScript()
    {
        $js = '';
        $view = $this->getView();
        $this->initClientOptions();
        if (!empty($this->mask)) {
            $this->clientOptions['mask'] = $this->mask;
        }
        $this->hashPluginOptions($view);
        if (is_array($this->definitions) && !empty($this->definitions)) {
            $js .= ucfirst(self::PLUGIN_NAME) . '.extendDefinitions(' . Json::htmlEncode($this->definitions) . ');';
        }
        if (is_array($this->aliases) && !empty($this->aliases)) {
            $js .= ucfirst(self::PLUGIN_NAME) . '.extendAliases(' . Json::htmlEncode($this->aliases) . ');';
        }
        $id = $this->options['id'];
        $js .= 'jQuery("#' . $id . '").' . self::PLUGIN_NAME . '(' . $this->_hashVar . ');';
        MaskedInputAsset::register($view);
        $view->registerJs($js);
    }
}
