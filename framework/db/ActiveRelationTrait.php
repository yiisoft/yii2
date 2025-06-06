<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

/**
 * ActiveRelationTrait implements the common methods and properties for active record relational queries.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 * @phpcs:disable Squiz.NamingConventions.ValidVariableName.PrivateNoUnderscore
 *
 * @method ActiveRecordInterface|array|null one($db = null) See [[ActiveQueryInterface::one()]] for more info.
 * @method ActiveRecordInterface[] all($db = null) See [[ActiveQueryInterface::all()]] for more info.
 * @property ActiveRecord $modelClass
 */
trait ActiveRelationTrait
{
    /**
     * @var bool whether this query represents a relation to more than one record.
     * This property is only used in relational context. If true, this relation will
     * populate all query results into AR instances using [[Query::all()|all()]].
     * If false, only the first row of the results will be retrieved using [[Query::one()|one()]].
     */
    public $multiple;
    /**
     * @var ActiveRecord the primary model of a relational query.
     * This is used only in lazy loading with dynamic query options.
     */
    public $primaryModel;
    /**
     * @var array the columns of the primary and foreign tables that establish a relation.
     * The array keys must be columns of the table for this relation, and the array values
     * must be the corresponding columns from the primary table.
     * Do not prefix or quote the column names as this will be done automatically by Yii.
     * This property is only used in relational context.
     */
    public $link;
    /**
     * @var array|object the query associated with the junction table. Please call [[via()]]
     * to set this property instead of directly setting it.
     * This property is only used in relational context.
     * @see via()
     */
    public $via;
    /**
     * @var string the name of the relation that is the inverse of this relation.
     * For example, an order has a customer, which means the inverse of the "customer" relation
     * is the "orders", and the inverse of the "orders" relation is the "customer".
     * If this property is set, the primary record(s) will be referenced through the specified relation.
     * For example, `$customer->orders[0]->customer` and `$customer` will be the same object,
     * and accessing the customer of an order will not trigger new DB query.
     * This property is only used in relational context.
     * @see inverseOf()
     */
    public $inverseOf;

    private $viaMap;

    /**
     * Clones internal objects.
     */
    public function __clone()
    {
        parent::__clone();
        // make a clone of "via" object so that the same query object can be reused multiple times
        if (is_object($this->via)) {
            $this->via = clone $this->via;
        } elseif (is_array($this->via)) {
            $this->via = [$this->via[0], clone $this->via[1], $this->via[2]];
        }
    }

    /**
     * Specifies the relation associated with the junction table.
     *
     * Use this method to specify a pivot record/table when declaring a relation in the [[ActiveRecord]] class:
     *
     * ```php
     * class Order extends ActiveRecord
     * {
     *    public function getOrderItems() {
     *        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
     *    }
     *
     *    public function getItems() {
     *        return $this->hasMany(Item::class, ['id' => 'item_id'])
     *                    ->via('orderItems');
     *    }
     * }
     * ```
     *
     * @param string $relationName the relation name. This refers to a relation declared in [[primaryModel]].
     * @param callable|null $callable a PHP callback for customizing the relation associated with the junction table.
     * Its signature should be `function($query)`, where `$query` is the query to be customized.
     * @return $this the relation object itself.
     */
    public function via($relationName, ?callable $callable = null)
    {
        $relation = $this->primaryModel->getRelation($relationName);
        $callableUsed = $callable !== null;
        $this->via = [$relationName, $relation, $callableUsed];
        if ($callable !== null) {
            call_user_func($callable, $relation);
        }

        return $this;
    }

    /**
     * Sets the name of the relation that is the inverse of this relation.
     * For example, a customer has orders, which means the inverse of the "orders" relation is the "customer".
     * If this property is set, the primary record(s) will be referenced through the specified relation.
     * For example, `$customer->orders[0]->customer` and `$customer` will be the same object,
     * and accessing the customer of an order will not trigger a new DB query.
     *
     * Use this method when declaring a relation in the [[ActiveRecord]] class, e.g. in Customer model:
     *
     * ```php
     * public function getOrders()
     * {
     *     return $this->hasMany(Order::class, ['customer_id' => 'id'])->inverseOf('customer');
     * }
     * ```
     *
     * This also may be used for Order model, but with caution:
     *
     * ```php
     * public function getCustomer()
     * {
     *     return $this->hasOne(Customer::class, ['id' => 'customer_id'])->inverseOf('orders');
     * }
     * ```
     *
     * in this case result will depend on how order(s) was loaded.
     * Let's suppose customer has several orders. If only one order was loaded:
     *
     * ```php
     * $orders = Order::find()->where(['id' => 1])->all();
     * $customerOrders = $orders[0]->customer->orders;
     * ```
     *
     * variable `$customerOrders` will contain only one order. If orders was loaded like this:
     *
     * ```php
     * $orders = Order::find()->with('customer')->where(['customer_id' => 1])->all();
     * $customerOrders = $orders[0]->customer->orders;
     * ```
     *
     * variable `$customerOrders` will contain all orders of the customer.
     *
     * @param string $relationName the name of the relation that is the inverse of this relation.
     * @return $this the relation object itself.
     */
    public function inverseOf($relationName)
    {
        $this->inverseOf = $relationName;
        return $this;
    }

    /**
     * Finds the related records for the specified primary record.
     * This method is invoked when a relation of an ActiveRecord is being accessed lazily.
     * @param string $name the relation name
     * @param ActiveRecordInterface|BaseActiveRecord $model the primary model
     * @return mixed the related record(s)
     * @throws InvalidArgumentException if the relation is invalid
     */
    public function findFor($name, $model)
    {
        if (method_exists($model, 'get' . $name)) {
            $method = new \ReflectionMethod($model, 'get' . $name);
            $realName = lcfirst(substr($method->getName(), 3));
            if ($realName !== $name) {
                throw new InvalidArgumentException('Relation names are case sensitive. ' . get_class($model) . " has a relation named \"$realName\" instead of \"$name\".");
            }
        }

        return $this->multiple ? $this->all() : $this->one();
    }

    /**
     * If applicable, populate the query's primary model into the related records' inverse relationship.
     * @param array $result the array of related records as generated by [[populate()]]
     * @since 2.0.9
     */
    private function addInverseRelations(&$result)
    {
        if ($this->inverseOf === null) {
            return;
        }

        foreach ($result as $i => $relatedModel) {
            if ($relatedModel instanceof ActiveRecordInterface) {
                if (!isset($inverseRelation)) {
                    $inverseRelation = $relatedModel->getRelation($this->inverseOf);
                }
                $relatedModel->populateRelation($this->inverseOf, $inverseRelation->multiple ? [$this->primaryModel] : $this->primaryModel);
            } else {
                if (!isset($inverseRelation)) {
                    /** @var ActiveRecordInterface $modelClass */
                    $modelClass = $this->modelClass;
                    $inverseRelation = $modelClass::instance()->getRelation($this->inverseOf);
                }
                $result[$i][$this->inverseOf] = $inverseRelation->multiple ? [$this->primaryModel] : $this->primaryModel;
            }
        }
    }

    /**
     * Finds the related records and populates them into the primary models.
     * @param string $name the relation name
     * @param array $primaryModels primary models
     * @return array the related models
     * @throws InvalidConfigException if [[link]] is invalid
     */
    public function populateRelation($name, &$primaryModels)
    {
        if (!is_array($this->link)) {
            throw new InvalidConfigException('Invalid link: it must be an array of key-value pairs.');
        }

        if ($this->via instanceof self) {
            // via junction table
            /** @var self $viaQuery */
            $viaQuery = $this->via;
            $viaModels = $viaQuery->findJunctionRows($primaryModels);
            $this->filterByModels($viaModels);
        } elseif (is_array($this->via)) {
            // via relation
            /** @var self|ActiveQueryTrait $viaQuery */
            list($viaName, $viaQuery) = $this->via;
            if ($viaQuery->asArray === null) {
                // inherit asArray from primary query
                $viaQuery->asArray($this->asArray);
            }
            $viaQuery->primaryModel = null;
            $viaModels = array_filter($viaQuery->populateRelation($viaName, $primaryModels));
            $this->filterByModels($viaModels);
        } else {
            $this->filterByModels($primaryModels);
        }

        if (!$this->multiple && count($primaryModels) === 1) {
            $model = $this->one();
            $primaryModel = reset($primaryModels);
            if ($primaryModel instanceof ActiveRecordInterface) {
                $primaryModel->populateRelation($name, $model);
            } else {
                $primaryModels[key($primaryModels)][$name] = $model;
            }
            if ($this->inverseOf !== null) {
                $this->populateInverseRelation($primaryModels, [$model], $name, $this->inverseOf);
            }

            return [$model];
        }

        // https://github.com/yiisoft/yii2/issues/3197
        // delay indexing related models after buckets are built
        $indexBy = $this->indexBy;
        $this->indexBy = null;
        $models = $this->all();

        if (isset($viaModels, $viaQuery)) {
            $buckets = $this->buildBuckets($models, $this->link, $viaModels, $viaQuery);
        } else {
            $buckets = $this->buildBuckets($models, $this->link);
        }

        $this->indexBy = $indexBy;
        if ($this->indexBy !== null && $this->multiple) {
            $buckets = $this->indexBuckets($buckets, $this->indexBy);
        }

        $link = array_values($this->link);
        if (isset($viaQuery)) {
            $deepViaQuery = $viaQuery;
            while ($deepViaQuery->via) {
                $deepViaQuery = is_array($deepViaQuery->via) ? $deepViaQuery->via[1] : $deepViaQuery->via;
            };
            $link = array_values($deepViaQuery->link);
        }
        foreach ($primaryModels as $i => $primaryModel) {
            $keys = null;
            if ($this->multiple && count($link) === 1) {
                $primaryModelKey = reset($link);
                $keys = isset($primaryModel[$primaryModelKey]) ? $primaryModel[$primaryModelKey] : null;
            }
            if (is_array($keys)) {
                $value = [];
                foreach ($keys as $key) {
                    $key = $this->normalizeModelKey($key);
                    if (isset($buckets[$key])) {
                        if ($this->indexBy !== null) {
                            // if indexBy is set, array_merge will cause renumbering of numeric array
                            foreach ($buckets[$key] as $bucketKey => $bucketValue) {
                                $value[$bucketKey] = $bucketValue;
                            }
                        } else {
                            $value = array_merge($value, $buckets[$key]);
                        }
                    }
                }
            } else {
                $key = $this->getModelKey($primaryModel, $link);
                $value = isset($buckets[$key]) ? $buckets[$key] : ($this->multiple ? [] : null);
            }
            if ($primaryModel instanceof ActiveRecordInterface) {
                $primaryModel->populateRelation($name, $value);
            } else {
                $primaryModels[$i][$name] = $value;
            }
        }
        if ($this->inverseOf !== null) {
            $this->populateInverseRelation($primaryModels, $models, $name, $this->inverseOf);
        }

        return $models;
    }

    /**
     * @param ActiveRecordInterface[] $primaryModels primary models
     * @param ActiveRecordInterface[] $models models
     * @param string $primaryName the primary relation name
     * @param string $name the relation name
     */
    private function populateInverseRelation(&$primaryModels, $models, $primaryName, $name)
    {
        if (empty($models) || empty($primaryModels)) {
            return;
        }
        $model = reset($models);
        /** @var ActiveQueryInterface|ActiveQuery $relation */
        if ($model instanceof ActiveRecordInterface) {
            $relation = $model->getRelation($name);
        } else {
            /** @var ActiveRecordInterface $modelClass */
            $modelClass = $this->modelClass;
            $relation = $modelClass::instance()->getRelation($name);
        }

        if ($relation->multiple) {
            $buckets = $this->buildBuckets($primaryModels, $relation->link, null, null, false);
            if ($model instanceof ActiveRecordInterface) {
                foreach ($models as $model) {
                    $key = $this->getModelKey($model, $relation->link);
                    $model->populateRelation($name, isset($buckets[$key]) ? $buckets[$key] : []);
                }
            } else {
                foreach ($primaryModels as $i => $primaryModel) {
                    if ($this->multiple) {
                        foreach ($primaryModel as $j => $m) {
                            $key = $this->getModelKey($m, $relation->link);
                            $primaryModels[$i][$j][$name] = isset($buckets[$key]) ? $buckets[$key] : [];
                        }
                    } elseif (!empty($primaryModel[$primaryName])) {
                        $key = $this->getModelKey($primaryModel[$primaryName], $relation->link);
                        $primaryModels[$i][$primaryName][$name] = isset($buckets[$key]) ? $buckets[$key] : [];
                    }
                }
            }
        } elseif ($this->multiple) {
            foreach ($primaryModels as $i => $primaryModel) {
                foreach ($primaryModel[$primaryName] as $j => $m) {
                    if ($m instanceof ActiveRecordInterface) {
                        $m->populateRelation($name, $primaryModel);
                    } else {
                        $primaryModels[$i][$primaryName][$j][$name] = $primaryModel;
                    }
                }
            }
        } else {
            foreach ($primaryModels as $i => $primaryModel) {
                if ($primaryModels[$i][$primaryName] instanceof ActiveRecordInterface) {
                    $primaryModels[$i][$primaryName]->populateRelation($name, $primaryModel);
                } elseif (!empty($primaryModels[$i][$primaryName])) {
                    $primaryModels[$i][$primaryName][$name] = $primaryModel;
                }
            }
        }
    }

    /**
     * @param array $models
     * @param array $link
     * @param array|null $viaModels
     * @param self|null $viaQuery
     * @param bool $checkMultiple
     * @return array
     */
    private function buildBuckets($models, $link, $viaModels = null, $viaQuery = null, $checkMultiple = true)
    {
        if ($viaModels !== null) {
            $map = [];
            $viaLink = $viaQuery->link;
            $viaLinkKeys = array_keys($viaLink);
            $linkValues = array_values($link);
            foreach ($viaModels as $viaModel) {
                $key1 = $this->getModelKey($viaModel, $viaLinkKeys);
                $key2 = $this->getModelKey($viaModel, $linkValues);
                $map[$key2][$key1] = true;
            }

            $viaQuery->viaMap = $map;

            $viaVia = $viaQuery->via;
            while ($viaVia) {
                $viaViaQuery = is_array($viaVia) ? $viaVia[1] : $viaVia;
                $map = $this->mapVia($map, $viaViaQuery->viaMap);

                $viaVia = $viaViaQuery->via;
            };
        }

        $buckets = [];
        $linkKeys = array_keys($link);

        if (isset($map)) {
            foreach ($models as $model) {
                $key = $this->getModelKey($model, $linkKeys);
                if (isset($map[$key])) {
                    foreach (array_keys($map[$key]) as $key2) {
                        $buckets[$key2][] = $model;
                    }
                }
            }
        } else {
            foreach ($models as $model) {
                $key = $this->getModelKey($model, $linkKeys);
                $buckets[$key][] = $model;
            }
        }

        if ($checkMultiple && !$this->multiple) {
            foreach ($buckets as $i => $bucket) {
                $buckets[$i] = reset($bucket);
            }
        }

        return $buckets;
    }

    /**
     * @param array $map
     * @param array $viaMap
     * @return array
     */
    private function mapVia($map, $viaMap)
    {
        $resultMap = [];
        foreach ($map as $key => $linkKeys) {
            $resultMap[$key] = [];
            foreach (array_keys($linkKeys) as $linkKey) {
                $resultMap[$key] += $viaMap[$linkKey];
            }
        }
        return $resultMap;
    }

    /**
     * Indexes buckets by column name.
     *
     * @param array $buckets
     * @param string|callable $indexBy the name of the column by which the query results should be indexed by.
     * This can also be a callable (e.g. anonymous function) that returns the index value based on the given row data.
     * @return array
     */
    private function indexBuckets($buckets, $indexBy)
    {
        $result = [];
        foreach ($buckets as $key => $models) {
            $result[$key] = [];
            foreach ($models as $model) {
                $index = is_string($indexBy) ? $model[$indexBy] : call_user_func($indexBy, $model);
                $result[$key][$index] = $model;
            }
        }

        return $result;
    }

    /**
     * @param array $attributes the attributes to prefix
     * @return array
     */
    private function prefixKeyColumns($attributes)
    {
        if ($this instanceof ActiveQuery && (!empty($this->join) || !empty($this->joinWith))) {
            if (empty($this->from)) {
                /** @var ActiveRecord $modelClass */
                $modelClass = $this->modelClass;
                $alias = $modelClass::tableName();
            } else {
                foreach ($this->from as $alias => $table) {
                    if (!is_string($alias)) {
                        $alias = $table;
                    }
                    break;
                }
            }
            if (isset($alias)) {
                foreach ($attributes as $i => $attribute) {
                    $attributes[$i] = "$alias.$attribute";
                }
            }
        }

        return $attributes;
    }

    /**
     * @param array $models
     */
    private function filterByModels($models)
    {
        $attributes = array_keys($this->link);

        $attributes = $this->prefixKeyColumns($attributes);

        $values = [];
        if (count($attributes) === 1) {
            // single key
            $attribute = reset($this->link);
            foreach ($models as $model) {
                $value = isset($model[$attribute]) || (is_object($model) && property_exists($model, $attribute)) ? $model[$attribute] : null;
                if ($value !== null) {
                    if (is_array($value)) {
                        $values = array_merge($values, $value);
                    } elseif ($value instanceof ArrayExpression && $value->getDimension() === 1) {
                        $values = array_merge($values, $value->getValue());
                    } else {
                        $values[] = $value;
                    }
                }
            }
            if (empty($values)) {
                $this->emulateExecution();
            }
        } else {
            // composite keys

            // ensure keys of $this->link are prefixed the same way as $attributes
            $prefixedLink = array_combine($attributes, $this->link);
            foreach ($models as $model) {
                $v = [];
                foreach ($prefixedLink as $attribute => $link) {
                    $v[$attribute] = $model[$link];
                }
                $values[] = $v;
                if (empty($v)) {
                    $this->emulateExecution();
                }
            }
        }

        if (!empty($values)) {
            $scalarValues = [];
            $nonScalarValues = [];
            foreach ($values as $value) {
                if (is_scalar($value)) {
                    $scalarValues[] = $value;
                } else {
                    $nonScalarValues[] = $value;
                }
            }

            $scalarValues = array_unique($scalarValues);
            $values = array_merge($scalarValues, $nonScalarValues);
        }

        $this->andWhere(['in', $attributes, $values]);
    }

    /**
     * @param ActiveRecordInterface|array $model
     * @param array $attributes
     * @return string|false
     */
    private function getModelKey($model, $attributes)
    {
        $key = [];
        foreach ($attributes as $attribute) {
            if (isset($model[$attribute]) || (is_object($model) && property_exists($model, $attribute))) {
                $key[] = $this->normalizeModelKey($model[$attribute]);
            }
        }
        if (count($key) > 1) {
            return serialize($key);
        }
        return reset($key);
    }

    /**
     * @param mixed $value raw key value. Since 2.0.40 non-string values must be convertible to string (like special
     * objects for cross-DBMS relations, for example: `|MongoId`).
     * @return string normalized key value.
     */
    private function normalizeModelKey($value)
    {
        try {
            return (string)$value;
        } catch (\Exception $e) {
            throw new InvalidConfigException('Value must be convertable to string.');
        } catch (\Throwable $e) {
            throw new InvalidConfigException('Value must be convertable to string.');
        }
    }

    /**
     * @param array $primaryModels either array of AR instances or arrays
     * @return array
     */
    private function findJunctionRows($primaryModels)
    {
        if (empty($primaryModels)) {
            return [];
        }
        $this->filterByModels($primaryModels);
        /** @var ActiveRecord $primaryModel */
        $primaryModel = reset($primaryModels);
        if (!$primaryModel instanceof ActiveRecordInterface) {
            // when primaryModels are array of arrays (asArray case)
            $primaryModel = $this->modelClass;
        }

        return $this->asArray()->all($primaryModel::getDb());
    }
}
