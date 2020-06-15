<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * @internal This trait is only used to denote deprecated magic properties of [[ExistValidator]] for IDEs via a
 * `@mixin` tag. It is never actually loaded at runtime.
 *
 * @author Brandon Kelly <brandon@craftcms.com>
 * @since 2.0.36
 */
trait ForceMasterDbTrait
{
    /**
     * @var bool whether this validator is forced to always use primary DB connection
     * @deprecated since 2.0.36. Use [[ExistValidator::$forcePrimaryDb]] instead.
     */
    public $forceMasterDb = true;
}
