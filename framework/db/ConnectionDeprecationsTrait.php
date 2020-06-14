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
     * @var bool whether to enable read/write splitting by using [[Connection::$replicas]] to read data.
     * @deprecated since 2.0.36. Use [[Connection::$enableReplicas]] instead.
     */
    public $enableSlaves;
    /**
     * @var array list of replica connection configurations. Each configuration is used to create a replica DB connection.
     * @deprecated since 2.0.36. Use [[Connection::$replicas]] instead.
     */
    public $slaves;
    /**
     * @var array the configuration that should be merged with every replica configuration listed in
     * [[Connection::$replicas]].
     * @deprecated since 2.0.36. Use [[Connection::$replicaConfig]] instead.
     */
    public $slaveConfig;
    /**
     * @var array list of primary connection configurations. Each configuration is used to create a primary DB connection.
     * @deprecated since 2.0.36. Use [[Connection::$primaries]] instead.
     */
    public $masters;
    /**
     * @var array the configuration that should be merged with every primary configuration listed in
     * [[Connection::$primaries]].
     * @deprecated since 2.0.36. Use [[Connection::$primaryConfig]] instead.
     */
    public $masterConfig;
    /**
     * @var bool whether to shuffle [[Connection::$primaries]] before getting one.
     * @deprecated since 2.0.36. Use [[Connection::$shufflePrimaries]] instead.
     */
    public $shuffleMasters;
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
