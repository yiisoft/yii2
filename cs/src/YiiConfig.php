<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\cs;

use PhpCsFixer\Config;
use yii\helpers\ArrayHelper;

/**
 * Basic rules used by Yii 2 ecosystem.
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 * @since 2.0.0
 */
class YiiConfig extends Config
{
    /**
     * {@inheritdoc}
     */
    public function __construct($name = 'yii-cs-config')
    {
        parent::__construct($name);

        $this->setRules([
            '@PSR2' => true,
            'array_syntax' => [
                'syntax' => 'short',
            ],
            'binary_operator_spaces' => [
                'align_double_arrow' => false,
                'align_equals' => false,
            ],
            'cast_spaces' => true,
            'concat_space' => [
                'spacing' => 'one',
            ],
        ]);
    }

    /**
     * Merge current rules config with provided list of rules.
     *
     * @param array $rules
     * @return $this
     * @see setRules()
     * @see ArrayHelper::merge()
     */
    public function mergeRules(array $rules)
    {
        parent::setRules(ArrayHelper::merge($this->getRules(), $rules));

        return $this;
    }
}
