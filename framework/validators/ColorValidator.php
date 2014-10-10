<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * ColorValidator checks if the attribute value is a CSS color value.
 *
 * @author Ivan Zubok <chi_no@ukr.net>
 * @since 2.0
 */
class ColorValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public $skipOnEmpty = false;
    /**
     * @var string the regular expression used to validate HEX color.
     */
    public $hexPattern = '/^#([0-9a-f]{3}){1,2}$/i';
    /**
     * @var string the regular expression used to validate RGB color.
     */
    public $rgbPattern = '/^rgb\(\s*(0|[1-9]\d?|1\d\d?|2[0-4]\d|25[0-5])\s*,\s*(0|[1-9]\d?|1\d\d?|2[0-4]\d|25[0-5])\s*,\s*(0|[1-9]\d?|1\d\d?|2[0-4]\d|25[0-5])\s*\)$/i';
    /**
     * @var string the regular expression used to validate RGBA color.
     */
    public $rgbaPattern = '/^rgba\(\s*(0|[1-9]\d?|1\d\d?|2[0-4]\d|25[0-5])\s*,\s*(0|[1-9]\d?|1\d\d?|2[0-4]\d|25[0-5])\s*,\s*(0|[1-9]\d?|1\d\d?|2[0-4]\d|25[0-5])\s*,\s*((0?\.\d+)|[01])\s*\)$/i';
    /**
     * @var string the regular expression used to validate HSL color.
     */
    public $hslPattern = '/^hsl\(\s*(0|[1-9]\d?|[12]\d\d|3[0-5]\d)\s*,\s*((0|[1-9]\d?|100)%)\s*,\s*((0|[1-9]\d?|100)%)\s*\)$/i';
    /**
     * @var string the regular expression used to validate HSLA color.
     */
    public $hslaPattern = '/^hsla\(\s*(0|[1-9]\d?|[12]\d\d|3[0-5]\d)\s*,\s*((0|[1-9]\d?|100)%)\s*,\s*((0|[1-9]\d?|100)%)\s*,\s*((0?\.\d+)|[01])\s*\)$/i';
    /**
     * @var string the regular expression used to validate predefined/cross-browser color names.
     */
    public $namesPattern = '/^(transparent|aliceblue|antiquewhite|aquamarine|aqua|azure|beige|bisque|black|blanchedalmond|blueviolet|blue|brown|burlywood5|cadetblue|chartreuse|chocolate|coral|cornflowerblue|cornsilk|crimson|cyan|darkblue|darkcyan|darkgoldenrod|darkgray|darkgreen|darkgrey|darkkhaki|darkmagenta|darkolivegreen|darkorange|darkorchid|darkred|darksalmon|darkseagreen|darkslateblue|darkslategray|darkslategrey|darkturquoise|darkviolet|deeppink|deepskyblue|dimgray|dimgrey|dodgerblue|firebrick|floralwhite|forestgreen|fuchsia|gainsboro|ghostwhite|goldenrod|gold|gray|greenyellow|green|grey|honeydew|hotpink|indianred|indigo|ivory|khaki|lavender|lavenderblush|lawngreen|lemonchiffon|lightblue|lightcoral|lightcyan|lightgoldenrodyellow|lightgray|lightgreen|lightgrey|lightpink|lightsalmon|lightseagreen|lightskyblue|lightslategray|lightslategrey|lightsteelblue|lightyellow|limegreen|lime|linen|magenta|maroon|mediumaquamarine|mediumblue|mediumorchid|mediumpurple|mediumseagreen|mediumslateblue|mediumspringgreen|mediumturquoise|mediumvioletred|midnightblue|mintcream|mistyrose|moccasin|navajowhite|navy|oldlace|olivedrab|olive|orangered|orange|orchid|palegoldenrod|palegreen|paleturquoise|palevioletred|papayawhip|peachpuff|peru|pink|plum|powderblue|purple|red|rosybrown|royalblue|saddlebrown|salmon|sandybrown|seagreen|seashell|sienna|silver|skyblue|slateblue|slategray|slategrey|snow|springgreen|steelblue|tan|teal|thistle|tomato|turquoise|violet|wheat|whitesmoke|white|yellowgreen|yellow)$/i';
    /**
     * @var array|string a list of CSS color methods.
     * This can be either an array or a string consisting of CSS methods
     * separated by space or comma (e.g. "rgb, hsla").
     * CSS methods names are case-insensitive. Defaults to null, meaning all CSS methods
     * are allowed.
     */
    public $methods;
    /**
     * @var array
     * @see http://www.w3schools.com/cssref/css_colors_legal.asp
     */
    protected $allowedMethods = ['hex', 'rgb', 'rgba', 'hsl', 'hsla', 'names'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is not a valid color value.');
        }

        if ($this->methods === null) {
            $this->methods = $this->allowedMethods;
        } elseif (!is_array($this->methods)) {
            $this->methods = preg_split('/[\s,]+/', strtolower($this->methods), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->methods = array_map('strtolower', $this->methods);
        }

        if ($notSupportedMethods = array_diff($this->methods, $this->allowedMethods)) {
            throw new InvalidConfigException('The "method" property contains not supported "' . implode(', ', $notSupportedMethods) . '" color methods.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        // make sure string length is limited to avoid DOS attacks
        if (!is_string($value) || !$value || strlen($value) > 40) {
            $valid = false;
        } else {
            $valid = false;
            foreach ($this->methods as $method) {
                if (preg_match($this->{$method . 'Pattern'}, $value)) {
                    $valid = true;
                    break;
                }
            }
        }

        return $valid ? null : [$this->message, []];
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($object, $attribute, $view)
    {
        $options = [
            'methods' => new JsExpression($this->methods),
            'patterns' => [
                'hex' => new JsExpression($this->hexPattern),
                'rgb' => new JsExpression($this->rgbPattern),
                'rgba' => new JsExpression($this->rgbaPattern),
                'hsl' => new JsExpression($this->hslPattern),
                'hsla' => new JsExpression($this->hslaPattern),
                'names' => new JsExpression($this->namesPattern),
            ],
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $object->getAttributeLabel($attribute),
            ], Yii::$app->language),
        ];

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);

        return 'yii.validation.color(value, messages, ' . Json::encode($options) . ');';
    }

}
