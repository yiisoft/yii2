<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * This class converts the attribute value(s) to string(s) and strip characters.
 *
 * @since 2.0.46
 */
class TrimValidator extends Validator
{
    /**
     * @var string|null The list of characters to strip, with `..` can specify a range of characters.
     * For example, use '\/ ' to normalize path/namespace.
     */
    public $chars;
    /**
     * @inheritDoc
     */
    public $skipOnEmpty = false;

    /**
     * @inheritDoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->{$attribute};
        if (!$this->skipOnEmpty || !$this->isEmpty($value)) {
            $model->{$attribute} = is_array($value)
                ? array_map([$this, 'trimValue'], $value)
                : $this->trimValue($value);
        }
    }

    /**
     * Converts given value to string and strips declared characters.
     *
     * @param mixed $value the value to strip
     * @return string
     */
    protected function trimValue($value)
    {
        return $this->isEmpty($value) ? '' : trim((string) $value, $this->chars);
    }

    /**
     * @inheritDoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);
        $options = json_encode($options);

        return 'value = yii.validation.trim($form, attribute, ' . $options . ', value);';
    }

    /**
     * @inheritDoc
     */
    public function getClientOptions($model, $attribute)
    {
        return [
            'skipOnEmpty' => (bool) $this->skipOnEmpty,
            'chars' => $this->chars ?: false,
        ];
    }
}
