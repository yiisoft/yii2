<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\twig;

/**
 * Template base class
 *
 * @author Alexei Tenitski <alexei@ten.net.nz>
 */
abstract class Template extends \Twig_Template
{
    /**
     * @inheritdoc
     */
    protected function getAttribute($object, $item, array $arguments = [], $type = \Twig_Template::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        // Twig uses isset() to check if attribute exists which does not work when attribute exists but is null
        if ($object instanceof \yii\db\BaseActiveRecord) {
            if ($type === \Twig_Template::METHOD_CALL) {
                return $object->$item();
            } else {
                return $object->$item;
            }
        }

        return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
    }
}
