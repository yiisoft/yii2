<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;
use yii\db\QueryInterface;
use yii\db\QueryTrait;

/**
 * Query represents a query to the search API of elasticsearch.
 *
 * Query provides a set of methods to facilitate the specification of different parameters of the query.
 * These methods can be chained together.
 *
 * By calling [[createCommand()]], we can get a [[Command]] instance which can be further
 * used to perform/execute the DB query against a database.
 *
 * For example,
 *
 * ~~~
 * $query = new Query;
 * $query->fields('id, name')
 *     ->from('myindex', 'users')
 *     ->limit(10);
 * // build and execute the query
 * $command = $query->createCommand();
 * $rows = $command->search(); // this way you get the raw output of elasticsearch.
 * ~~~
 *
 * You would normally call `$query->search()` instead of creating a command as this method
 * adds the `indexBy()` feature and also removes some inconsistencies from the response.
 *
 * Query also provides some methods to easier get some parts of the result only:
 *
 * - [[one()]]: returns a single record populated with the first row of data.
 * - [[all()]]: returns all records based on the query results.
 * - [[count()]]: returns the number of records.
 * - [[scalar()]]: returns the value of the first column in the first row of the query result.
 * - [[column()]]: returns the value of the first column in the query result.
 * - [[exists()]]: returns a value indicating whether the query result has data or not.
 *
 * NOTE: elasticsearch limits the number of records returned to 10 records by default.
 * If you expect to get more records you should specify limit explicitly.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Query extends Component implements QueryInterface
{
    use QueryTrait;

    /**
     * @var array the fields being retrieved from the documents. For example, `['id', 'name']`.
     * If not set, this option will not be applied to the query and no fields will be returned.
     * In this case the `_source` field will be returned by default which can be configured using [[source]].
     * Setting this to an empty array will result in no fields being retrieved, which means that only the primaryKey
     * of a record will be available in the result.
     *
     * For each field you may also add an array representing a [script field]. Example:
     *
     * ```php
     * $query->fields = [
     *     'id',
     *     'name',
     *     'value_times_two' => [
     *         'script' => "doc['my_field_name'].value * 2",
     *     ],
     *     'value_times_factor' => [
     *         'script' => "doc['my_field_name'].value * factor",
     *         'params' => [
     *             'factor' => 2.0
     *         ],
     *     ],
     * ]
     * ```
     *
     * > Note: Field values are [always returned as arrays] even if they only have one value.
     *
     * [always returned as arrays]: http://www.elasticsearch.org/guide/en/elasticsearch/reference/1.x/_return_values.html#_return_values
     * [script field]: http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-script-fields.html
     *
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-fields.html#search-request-fields
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-script-fields.html
     * @see fields()
     * @see source
     */
    public $fields;
    /**
     * @var array this option controls how the `_source` field is returned from the documents. For example, `['id', 'name']`
     * means that only the `id` and `name` field should be returned from `_source`.
     * If not set, it means retrieving the full `_source` field unless [[fields]] are specified.
     * Setting this option to `false` will disable return of the `_source` field, this means that only the primaryKey
     * of a record will be available in the result.
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-source-filtering.html
     * @see source()
     * @see fields
     */
    public $source;
    /**
     * @var string|array The index to retrieve data from. This can be a string representing a single index
     * or a an array of multiple indexes. If this is not set, indexes are being queried.
     * @see from()
     */
    public $index;
    /**
     * @var string|array The type to retrieve data from. This can be a string representing a single type
     * or a an array of multiple types. If this is not set, all types are being queried.
     * @see from()
     */
    public $type;
    /**
     * @var integer A search timeout, bounding the search request to be executed within the specified time value
     * and bail with the hits accumulated up to that point when expired. Defaults to no timeout.
     * @see timeout()
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-body.html#_parameters_3
     */
    public $timeout;
    /**
     * @var array|string The query part of this search query. This is an array or json string that follows the format of
     * the elasticsearch [Query DSL](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl.html).
     */
    public $query;
    /**
     * @var array|string The filter part of this search query. This is an array or json string that follows the format of
     * the elasticsearch [Query DSL](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl.html).
     */
    public $filter;
    /**
     * @var array The highlight part of this search query. This is an array that allows to highlight search results
     * on one or more fields.
     */
    public $highlight;

    public $facets = [];


    public function init()
    {
        parent::init();
        // setting the default limit according to elasticsearch defaults
        // http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-body.html#_parameters_3
        if ($this->limit === null) {
            $this->limit = 10;
        }
    }

    /**
     * Creates a DB command that can be used to execute this query.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @return Command the created DB command instance.
     */
    public function createCommand($db = null)
    {
        if ($db === null) {
            $db = Yii::$app->get('elasticsearch');
        }

        $commandConfig = $db->getQueryBuilder()->build($this);

        return $db->createCommand($commandConfig);
    }

    /**
     * Executes the query and returns all results as an array.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        $result = $this->createCommand($db)->search();
        if (empty($result['hits']['hits'])) {
            return [];
        }
        $rows = $result['hits']['hits'];
        if ($this->indexBy === null) {
            return $rows;
        }
        $models = [];
        foreach ($rows as $key => $row) {
            if ($this->indexBy !== null) {
                if (is_string($this->indexBy)) {
                    $key = isset($row['fields'][$this->indexBy]) ? reset($row['fields'][$this->indexBy]) : $row['_source'][$this->indexBy];
                } else {
                    $key = call_user_func($this->indexBy, $row);
                }
            }
            $models[$key] = $row;
        }
        return $models;
    }

    /**
     * Executes the query and returns a single row of result.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
     * results in nothing.
     */
    public function one($db = null)
    {
        $result = $this->createCommand($db)->search(['size' => 1]);
        if (empty($result['hits']['hits'])) {
            return false;
        }
        $record = reset($result['hits']['hits']);

        return $record;
    }

    /**
     * Executes the query and returns the complete search result including e.g. hits, facets, totalCount.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @param array $options The options given with this query. Possible options are:
     *
     *  - [routing](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search.html#search-routing)
     *  - [search_type](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-search-type.html)
     *
     * @return array the query results.
     */
    public function search($db = null, $options = [])
    {
        $result = $this->createCommand($db)->search($options);
        if (!empty($result['hits']['hits']) && $this->indexBy !== null) {
            $rows = [];
            foreach ($result['hits']['hits'] as $key => $row) {
                if (is_string($this->indexBy)) {
                    $key = isset($row['fields'][$this->indexBy]) ? $row['fields'][$this->indexBy] : $row['_source'][$this->indexBy];
                } else {
                    $key = call_user_func($this->indexBy, $row);
                }
                $rows[$key] = $row;
            }
            $result['hits']['hits'] = $rows;
        }
        return $result;
    }

    // TODO add query stats http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search.html#stats-groups

    // TODO add scroll/scan http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-search-type.html#scan

    /**
     * Executes the query and deletes all matching documents.
     *
     * This will not run facet queries.
     *
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function delete($db = null)
    {
        // TODO implement http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-delete-by-query.html
        // http://www.elasticsearch.org/guide/en/elasticsearch/reference/1.x/_search_requests.html
        throw new NotSupportedException('Delete by query is not implemented yet.');
    }

    /**
     * Returns the query result as a scalar value.
     * The value returned will be the specified field in the first document of the query results.
     * @param string $field name of the attribute to select
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @return string the value of the specified attribute in the first record of the query result.
     * Null is returned if the query result is empty or the field does not exist.
     */
    public function scalar($field, $db = null)
    {
        $record = self::one($db);
        if ($record !== false) {
            if ($field === '_id') {
                return $record['_id'];
            } elseif (isset($record['_source'][$field])) {
                return $record['_source'][$field];
            } elseif (isset($record['fields'][$field])) {
                return count($record['fields'][$field]) == 1 ? reset($record['fields'][$field]) : $record['fields'][$field];
            }
        }
        return null;
    }

    /**
     * Executes the query and returns the first column of the result.
     * @param string $field the field to query over
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @return array the first column of the query result. An empty array is returned if the query results in nothing.
     */
    public function column($field, $db = null)
    {
        $command = $this->createCommand($db);
        $command->queryParts['_source'] = [$field];
        $result = $command->search();
        if (empty($result['hits']['hits'])) {
            return [];
        }
        $column = [];
        foreach ($result['hits']['hits'] as $row) {
            $column[] = isset($row['_source'][$field]) ? $row['_source'][$field] : null;
        }
        return $column;
    }

    /**
     * Returns the number of records.
     * @param string $q the COUNT expression. This parameter is ignored by this implementation.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @return integer number of records
     */
    public function count($q = '*', $db = null)
    {
        // TODO consider sending to _count api instead of _search for performance
        // only when no facety are registerted.
        // http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-count.html
        // http://www.elasticsearch.org/guide/en/elasticsearch/reference/1.x/_search_requests.html

        $options = [];
        $options['search_type'] = 'count';

        return $this->createCommand($db)->search($options)['hits']['total'];
    }

    /**
     * Returns a value indicating whether the query result contains any row of data.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `elasticsearch` application component will be used.
     * @return boolean whether the query result contains any row of data.
     */
    public function exists($db = null)
    {
        return self::one($db) !== false;
    }

    /**
     * Adds a facet search to this query.
     * @param string $name the name of this facet
     * @param string $type the facet type. e.g. `terms`, `range`, `histogram`...
     * @param string|array $options the configuration options for this facet. Can be an array or a json string.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-query-facet.html
     */
    public function addFacet($name, $type, $options)
    {
        $this->facets[$name] = [$type => $options];
        return $this;
    }

    /**
     * The `terms facet` allow to specify field facets that return the N most frequent terms.
     * @param string $name the name of this facet
     * @param array $options additional option. Please refer to the elasticsearch documentation for details.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-terms-facet.html
     */
    public function addTermFacet($name, $options)
    {
        return $this->addFacet($name, 'terms', $options);
    }

    /**
     * Range facet allows to specify a set of ranges and get both the number of docs (count) that fall
     * within each range, and aggregated data either based on the field, or using another field.
     * @param string $name the name of this facet
     * @param array $options additional option. Please refer to the elasticsearch documentation for details.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-range-facet.html
     */
    public function addRangeFacet($name, $options)
    {
        return $this->addFacet($name, 'range', $options);
    }

    /**
     * The histogram facet works with numeric data by building a histogram across intervals of the field values.
     * Each value is "rounded" into an interval (or placed in a bucket), and statistics are provided per
     * interval/bucket (count and total).
     * @param string $name the name of this facet
     * @param array $options additional option. Please refer to the elasticsearch documentation for details.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-histogram-facet.html
     */
    public function addHistogramFacet($name, $options)
    {
        return $this->addFacet($name, 'histogram', $options);
    }

    /**
     * A specific histogram facet that can work with date field types enhancing it over the regular histogram facet.
     * @param string $name the name of this facet
     * @param array $options additional option. Please refer to the elasticsearch documentation for details.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-date-histogram-facet.html
     */
    public function addDateHistogramFacet($name, $options)
    {
        return $this->addFacet($name, 'date_histogram', $options);
    }

    /**
     * A filter facet (not to be confused with a facet filter) allows you to return a count of the hits matching the filter.
     * The filter itself can be expressed using the Query DSL.
     * @param string $name the name of this facet
     * @param string $filter the query in Query DSL
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-filter-facet.html
     */
    public function addFilterFacet($name, $filter)
    {
        return $this->addFacet($name, 'filter', $filter);
    }

    /**
     * A facet query allows to return a count of the hits matching the facet query.
     * The query itself can be expressed using the Query DSL.
     * @param string $name the name of this facet
     * @param string $query the query in Query DSL
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-query-facet.html
     */
    public function addQueryFacet($name, $query)
    {
        return $this->addFacet($name, 'query', $query);
    }

    /**
     * Statistical facet allows to compute statistical data on a numeric fields. The statistical data include count,
     * total, sum of squares, mean (average), minimum, maximum, variance, and standard deviation.
     * @param string $name the name of this facet
     * @param array $options additional option. Please refer to the elasticsearch documentation for details.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-statistical-facet.html
     */
    public function addStatisticalFacet($name, $options)
    {
        return $this->addFacet($name, 'statistical', $options);
    }

    /**
     * The `terms_stats` facet combines both the terms and statistical allowing to compute stats computed on a field,
     * per term value driven by another field.
     * @param string $name the name of this facet
     * @param array $options additional option. Please refer to the elasticsearch documentation for details.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-terms-stats-facet.html
     */
    public function addTermsStatsFacet($name, $options)
    {
        return $this->addFacet($name, 'terms_stats', $options);
    }

    /**
     * The `geo_distance` facet is a facet providing information for ranges of distances from a provided `geo_point`
     * including count of the number of hits that fall within each range, and aggregation information (like `total`).
     * @param string $name the name of this facet
     * @param array $options additional option. Please refer to the elasticsearch documentation for details.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-geo-distance-facet.html
     */
    public function addGeoDistanceFacet($name, $options)
    {
        return $this->addFacet($name, 'geo_distance', $options);
    }

    // TODO add suggesters http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters.html

    // TODO add validate query http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-validate.html

    // TODO support multi query via static method http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-multi-search.html

    /**
     * Sets the querypart of this search query.
     * @param string $query
     * @return static the query object itself
     */
    public function query($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Sets the filter part of this search query.
     * @param string $filter
     * @return static the query object itself
     */
    public function filter($filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Sets the index and type to retrieve documents from.
     * @param string|array $index The index to retrieve data from. This can be a string representing a single index
     * or a an array of multiple indexes. If this is `null` it means that all indexes are being queried.
     * @param string|array $type The type to retrieve data from. This can be a string representing a single type
     * or a an array of multiple types. If this is `null` it means that all types are being queried.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-search.html#search-multi-index-type
     */
    public function from($index, $type = null)
    {
        $this->index = $index;
        $this->type = $type;
        return $this;
    }

    /**
     * Sets the fields to retrieve from the documents.
     * @param array $fields the fields to be selected.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-fields.html
     */
    public function fields($fields)
    {
        if (is_array($fields) || $fields === null) {
            $this->fields = $fields;
        } else {
            $this->fields = func_get_args();
        }
        return $this;
    }

    /**
     * Sets a highlight parameters to retrieve from the documents.
     * @param array $highlight array of parameters to highlight results.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-highlighting.html
     */
    public function highlight($highlight)
    {
        $this->highlight = $highlight;
        return $this;
    }

    /**
     * Sets the source filtering, specifying how the `_source` field of the document should be returned.
     * @param array $source the source patterns to be selected.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-source-filtering.html
     */
    public function source($source)
    {
        if (is_array($source) || $source === null) {
            $this->source = $source;
        } else {
            $this->source = func_get_args();
        }
        return $this;
    }

    /**
     * Sets the search timeout.
     * @param integer $timeout A search timeout, bounding the search request to be executed within the specified time value
     * and bail with the hits accumulated up to that point when expired. Defaults to no timeout.
     * @return static the query object itself
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-body.html#_parameters_3
     */
    public function timeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }
}
