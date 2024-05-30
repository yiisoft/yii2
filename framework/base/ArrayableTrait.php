<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Link;
use yii\web\Linkable;

/**
 * ArrayableTrait provides a common implementation of the [[Arrayable]] interface.
 *
 * ArrayableTrait implements [[toArray()]] by respecting the field definitions as declared
 * in [[fields()]] and [[extraFields()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait ArrayableTrait
{
    /**
     * Returns the list of fields that should be returned by default by [[toArray()]] when no specific fields are specified.
     *
     * A field is a named element in the returned array by [[toArray()]].
     *
     * This method should return an array of field names or field definitions.
     * If the former, the field name will be treated as an object property name whose value will be used
     * as the field value. If the latter, the array key should be the field name while the array value should be
     * the corresponding field definition which can be either an object property name or a PHP callable
     * returning the corresponding field value. The signature of the callable should be:
     *
     * ```php
     * function ($model, $field) {
     *     // return field value
     * }
     * ```
     *
     * For example, the following code declares four fields:
     *
     * - `email`: the field name is the same as the property name `email`;
     * - `firstName` and `lastName`: the field names are `firstName` and `lastName`, and their
     *   values are obtained from the `first_name` and `last_name` properties;
     * - `fullName`: the field name is `fullName`. Its value is obtained by concatenating `first_name`
     *   and `last_name`.
     *
     * ```php
     * return [
     *     'email',
     *     'firstName' => 'first_name',
     *     'lastName' => 'last_name',
     *     'fullName' => function () {
     *         return $this->first_name . ' ' . $this->last_name;
     *     },
     * ];
     * ```
     *
     * In this method, you may also want to return different lists of fields based on some context
     * information. For example, depending on the privilege of the current application user,
     * you may return different sets of visible fields or filter out some fields.
     *
     * The default implementation of this method returns the public object member variables indexed by themselves.
     *
     * @return array the list of field names or field definitions.
     * @see toArray()
     */
    public function fields()
    {
        $fields = array_keys(Yii::getObjectVars($this));
        return array_combine($fields, $fields);
    }

    /**
     * Returns the list of fields that can be expanded further and returned by [[toArray()]].
     *
     * This method is similar to [[fields()]] except that the list of fields returned
     * by this method are not returned by default by [[toArray()]]. Only when field names
     * to be expanded are explicitly specified when calling [[toArray()]], will their values
     * be exported.
     *
     * The default implementation returns an empty array.
     *
     * You may override this method to return a list of expandable fields based on some context information
     * (e.g. the current application user).
     *
     * @return array the list of expandable field names or field definitions. Please refer
     * to [[fields()]] on the format of the return value.
     * @see toArray()
     * @see fields()
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * Converts the model into an array.
     *
     * This method will first identify which fields to be included in the resulting array by calling [[resolveFields()]].
     * It will then turn the model into an array with these fields. If `$recursive` is true,
     * any embedded objects will also be converted into arrays.
     * When embedded objects are [[Arrayable]], their respective nested fields will be extracted and passed to [[toArray()]].
     *
     * If the model implements the [[Linkable]] interface, the resulting array will also have a `_link` element
     * which refers to a list of links as specified by the interface.
     *
     * @param array $fields the fields being requested.
     * If empty or if it contains '*', all fields as specified by [[fields()]] will be returned.
     * Fields can be nested, separated with dots (.). e.g.: item.field.sub-field
     * `$recursive` must be true for nested fields to be extracted. If `$recursive` is false, only the root fields will be extracted.
     * @param array $expand the additional fields being requested for exporting. Only fields declared in [[extraFields()]]
     * will be considered.
     * Expand can also be nested, separated with dots (.). e.g.: item.expand1.expand2
     * `$recursive` must be true for nested expands to be extracted. If `$recursive` is false, only the root expands will be extracted.
     * @param bool $recursive whether to recursively return array representation of embedded objects.
     * @return array the array representation of the object
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = [];
        foreach ($this->resolveFields($fields, $expand) as $field => $definition) {
            $attribute = is_string($definition) ? $this->$definition : $definition($this, $field);

            if ($recursive) {
                $nestedFields = $this->extractFieldsFor($fields, $field);
                $nestedExpand = $this->extractFieldsFor($expand, $field);
                if ($attribute instanceof Arrayable) {
                    $attribute = $attribute->toArray($nestedFields, $nestedExpand);
                } elseif ($attribute instanceof \JsonSerializable) {
                    $attribute = $attribute->jsonSerialize();
                } elseif (is_array($attribute)) {
                    $attribute = array_map(
                        function ($item) use ($nestedFields, $nestedExpand) {
                            if ($item instanceof Arrayable) {
                                return $item->toArray($nestedFields, $nestedExpand);
                            } elseif ($item instanceof \JsonSerializable) {
                                return $item->jsonSerialize();
                            }
                            return $item;
                        },
                        $attribute
                    );
                }
            }
            $data[$field] = $attribute;
        }

        if ($this instanceof Linkable) {
            $data['_links'] = Link::serialize($this->getLinks());
        }

        return $recursive ? ArrayHelper::toArray($data) : $data;
    }

    /**
     * Extracts the root field names from nested fields.
     * Nested fields are separated with dots (.). e.g: "item.id"
     * The previous example would extract "item".
     *
     * @param array $fields The fields requested for extraction
     * @return array root fields extracted from the given nested fields
     * @since 2.0.14
     */
    protected function extractRootFields(array $fields)
    {
        $result = [];

        foreach ($fields as $field) {
            $result[] = current(explode('.', $field, 2));
        }

        if (in_array('*', $result, true)) {
            $result = [];
        }

        return array_unique($result);
    }

    /**
     * Extract nested fields from a fields collection for a given root field
     * Nested fields are separated with dots (.). e.g: "item.id"
     * The previous example would extract "id".
     *
     * @param array $fields The fields requested for extraction
     * @param string $rootField The root field for which we want to extract the nested fields
     * @return array nested fields extracted for the given field
     * @since 2.0.14
     */
    protected function extractFieldsFor(array $fields, $rootField)
    {
        $result = [];

        foreach ($fields as $field) {
            if (0 === strpos($field, "{$rootField}.")) {
                $result[] = preg_replace('/^' . preg_quote($rootField, '/') . '\./i', '', $field);
            }
        }

        return array_unique($result);
    }

    /**
     * Determines which fields can be returned by [[toArray()]].
     * This method will first extract the root fields from the given fields.
     * Then it will check the requested root fields against those declared in [[fields()]] and [[extraFields()]]
     * to determine which fields can be returned.
     * @param array $fields the fields being requested for exporting
     * @param array $expand the additional fields being requested for exporting
     * @return array the list of fields to be exported. The array keys are the field names, and the array values
     * are the corresponding object property names or PHP callables returning the field values.
     */
    protected function resolveFields(array $fields, array $expand)
    {
        $fields = $this->extractRootFields($fields);
        $expand = $this->extractRootFields($expand);
        $result = [];

        foreach ($this->fields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (empty($fields) || in_array($field, $fields, true)) {
                $result[$field] = $definition;
            }
        }

        if (empty($expand)) {
            return $result;
        }

        foreach ($this->extraFields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (in_array($field, $expand, true)) {
                $result[$field] = $definition;
            }
        }

        return $result;
    }
}
