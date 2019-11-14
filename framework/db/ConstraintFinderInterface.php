<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ConstraintFinderInterface 定义用于获取表约束信息的方法。
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.14
 */
interface ConstraintFinderInterface
{
    /**
     * 获取指定表的主键。
     * @param string $name 表名。表名可以包含结构名（如果有）。不要引用表名。
     * @param bool $refresh 是否重新加载信息（即使在缓存中找到）。
     * @return Constraint|null 表主键，如果表没有主键，就为 `null`。
     */
    public function getTablePrimaryKey($name, $refresh = false);

    /**
     * 返回数据库中所有表的主键。
     * @param string $schema 表的结构。默认为空字符串，表示当前或默认结构名称。
     * @param bool $refresh 是否获取最新的可用表结构。
     * 如果为 `false`，可以返回缓存数据（如果可用）。
     * @return Constraint[] 数据库中所有表的主键。
     * 每一个数组元素都是 [[Constraint]] 或它的子类实例。
     */
    public function getSchemaPrimaryKeys($schema = '', $refresh = false);

    /**
     * 获取指定表的外键信息。
     * @param string $name 表名。表名可以包含结构名（如果有）。不要引用表名。
     * @param bool $refresh 是否重新加载信息（即使在缓存中找到）。
     * @return ForeignKeyConstraint[] 表的外键。
     */
    public function getTableForeignKeys($name, $refresh = false);

    /**
     * 返回数据库中所有表的外键。
     * @param string $schema 表的结构。默认为空字符串，表示当前或默认结构名称。
     * @param bool $refresh 是否获取最新的可用表结构。
     * 如果为 false，可以返回缓存数据（如果可用）。
     * @return ForeignKeyConstraint[][] 数据库中所有表的外键。
     * 每一个数组元素都是 [[ForeignKeyConstraint]] 或它的子类实例。
     */
    public function getSchemaForeignKeys($schema = '', $refresh = false);

    /**
     * 获取指定表的索引信息。
     * @param string $name 表名。表名可以包含结构名（如果有）。不要引用表名。
     * @param bool $refresh 是否重新加载信息（即使在缓存中找到）。
     * @return IndexConstraint[] 表的索引。
     */
    public function getTableIndexes($name, $refresh = false);

    /**
     * 返回数据库中所有表的索引。
     * @param string $schema 表的结构。默认为空字符串，表示当前或默认结构名称。
     * @param bool $refresh 是否获取最新的可用表结构。
     * 如果为 false，可以返回缓存数据（如果可用）
     * @return IndexConstraint[][] 数据库中所有表的索引。
     * 每一个数组元素都是 [[IndexConstraint]] 或它的子类实例。
     */
    public function getSchemaIndexes($schema = '', $refresh = false);

    /**
     * 从指定表中获取唯一约束信息。
     * @param string $name 表名。表名可以包含结构名（如果有）。不要引用表名。
     * @param bool $refresh 是否重新加载信息（即使在缓存中找到）。
     * @return Constraint[] 表唯一约束。
     */
    public function getTableUniques($name, $refresh = false);

    /**
     * 返回数据库中所有表的唯一约束。
     * @param string $schema 表的结构。默认为空字符串，表示当前或默认结构名称。
     * @param bool $refresh 是否获取最新的可用表结构。
     * 如果为 false，可以返回缓存数据（如果可用）
     * @return Constraint[][] 所有数据库表中的唯一约束。
     * 每一个数组元素都是 [[Constraint]] 或它的子类实例。
     */
    public function getSchemaUniques($schema = '', $refresh = false);

    /**
     * 获取指定表的检查约束信息。
     * @param string $name 表名。表名可以包含结构名（如果有）。不要引用表名。
     * @param bool $refresh 是否重新加载信息（即使在缓存中找到）。
     * @return CheckConstraint[] 表检查约束。
     */
    public function getTableChecks($name, $refresh = false);

    /**
     * 返回数据库中所有表的检查约束。
     * @param string $schema 表的结构。默认为空字符串，表示当前或默认结构名称。
     * @param bool $refresh 是否获取最新的可用表结构。
     * 如果为 false，可以返回缓存数据（如果可用）
     * @return CheckConstraint[][] 检查数据库中所有表的约束。
     * 每一个数组元素都是 [[CheckConstraint]] 或它的子类实例。
     */
    public function getSchemaChecks($schema = '', $refresh = false);

    /**
     * 获取指定表的默认值约束信息。
     * @param string $name 表名。表名可以包含结构名（如果有）。不要引用表名。
     * @param bool $refresh 是否重新加载信息（即使在缓存中找到）。
     * @return DefaultValueConstraint[] 表默认值约束。
     */
    public function getTableDefaultValues($name, $refresh = false);

    /**
     * 返回数据库中所有表的默认值约束。
     * @param string $schema 表的结构。默认为空字符串，表示当前或默认结构名称。
     * @param bool $refresh 是否获取最新的可用表结构。
     * 如果为 false，可以返回缓存数据（如果可用）
     * @return DefaultValueConstraint[] 数据库中所有表的默认值约束。
     * 每一个数组元素都是 [[DefaultValueConstraint]] 或它的子类实例。
     */
    public function getSchemaDefaultValues($schema = '', $refresh = false);
}
