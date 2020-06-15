<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use PDO;

/**
 * @internal This trait is only used to denote deprecated magic properties of [[Connection]] for IDEs via a
 * `@mixin` tag. It is never actually loaded at runtime.
 *
 * @author Brandon Kelly <brandon@craftcms.com>
 * @since 2.0.36
 */
trait ConnectionDeprecationsTrait
{
    /**
     * @var Connection|null The currently active primary connection. `null` is returned if no primary connection is
     * available. This property is read-only.
     * @deprecated since 2.0.36. Use [[Connection::$primary]] instead.
     */
    public $master;
    /**
     * @var PDO The PDO instance for the currently active primary connection. This property is read-only.
     * @deprecated since 2.0.36. Use [[Connection::$primaryPdo]] instead.
     */
    public $masterPdo;
    /**
     * @var Connection The currently active replica connection. This property is read-only.
     * @deprecated since 2.0.36. Use [[Connection::$replica]] instead.
     */
    public $slave;
    /**
     * @var PDO The PDO instance for the currently active replica connection. This property is read-only.
     * @deprecated since 2.0.36. Use [[Connection::$slavePdo]] instead.
     */
    public $slavePdo;
}
