<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelInterface is the interface for the data model, which is defined by set of attributes.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.13
 */
interface ModelInterface
{
    /**
     * Returns the list of all attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributes();

    /**
     * Performs the data validation.
     * @param array $attributeNames list of attribute names that should be validated.
     * If this parameter is empty, it means any attribute listed in the applicable
     * validation rules should be validated.
     * @param bool $clearErrors whether to clear any existing validation errors before performing validation
     * @return bool whether the validation is successful without any error.
     * @throws InvalidParamException if the current scenario is unknown.
     */
    public function validate($attributeNames = null, $clearErrors = true);

    /**
     * Returns static model instance, which can be used to obtain model meta information.
     * @param bool $refresh whether to re-create static instance even if it is already cached.
     * @return static model instance.
     */
    public static function model($refresh = false);
}