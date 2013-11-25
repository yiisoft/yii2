<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use Yii;
use yii\base\Component;
use yii\db\QueryInterface;
use yii\db\QueryTrait;

/**
 * Class Query
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Query extends Component implements QueryInterface
{
	use QueryTrait;

	/**
	 * @var array the fields being retrieved from the documents. For example, `['id', 'name']`.
	 * If not set, it means retrieving all fields. An empty array will result in no fields being
	 * retrieved. This means that only the primaryKey of a record will be available in the result.
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-fields.html#search-request-fields
	 * @see fields()
	 */
	public $fields;
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

	public $query;

	public $filter;

	public $facets = [];

	public $facetResults = [];

	public $totalCount;

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($db = null)
	{
		if ($db === null) {
			$db = Yii::$app->getComponent('elasticsearch');
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
		// TODO publish facet results
		$rows = $result['hits'];
		if ($this->indexBy === null && $this->fields === null) {
			return $rows;
		}
		$models = [];
		foreach ($rows as $key => $row) {
			if ($this->fields !== null) {
				$row['_source'] = isset($row['fields']) ? $row['fields'] : [];
				unset($row['fields']);
			}
			if ($this->indexBy !== null) {
				if (is_string($this->indexBy)) {
					$key = $row['_source'][$this->indexBy];
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
		$options['size'] = 1;
		$result = $this->createCommand($db)->search($options);
		// TODO publish facet results
		if (empty($result['hits'])) {
			return false;
		}
		$record = reset($result['hits']);
		if ($this->fields !== null) {
			$record['_source'] = isset($record['fields']) ? $record['fields'] : [];
			unset($record['fields']);
		}
		return $record;
	}

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
		// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-delete-by-query.html
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
		if ($record !== false && isset($record['_source'][$field])) {
			return $record['_source'][$field];
		} else {
			return null;
		}
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
		$command->queryParts['fields'] = [$field];
		$rows = $command->search()['hits'];
		$result = [];
		foreach ($rows as $row) {
			$result[] = isset($row['fields'][$field]) ? $row['fields'][$field] : null;
		}
		return $result;
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

		$options = [];
		$options['search_type'] = 'count';
		$count = $this->createCommand($db)->search($options)['total'];
		if ($this->limit === null && $this->offset === null) {
			return $count;
		} elseif ($this->offset !== null) {
			$count = $this->offset < $count ? $count - $this->offset : 0;
		}
		return $this->limit === null ? $count : ($this->limit > $count ? $count : $this->limit);
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
	 * @return static
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
	 * @return static
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
	 * @return static
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
	 * @return static
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
	 * @return static
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
	 * @return static
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
	 * @return static
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
	 * @return static
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
	 * @return static
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
	 * @return static
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets-geo-distance-facet.html
	 */
	public function addGeoDistanceFacet($name, $options)
	{
		return $this->addFacet($name, 'geo_distance', $options);
	}

	// TODO add suggesters http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters.html

	// TODO add validate query http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-validate.html

	// TODO support multi query via static method http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-multi-search.html

	public function query()
	{

	}

	/**
	 * Sets the index and type to retrieve documents from.
	 * @param string|array $index The index to retrieve data from. This can be a string representing a single index
	 * or a an array of multiple indexes. If this is `null` it means that all indexes are being queried.
	 * @param string|array $type The type to retrieve data from. This can be a string representing a single type
	 * or a an array of multiple types. If this is `null` it means that all types are being queried.
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-search.html#search-multi-index-type
	 */
	public function from($index, $type = null)
	{
		$this->index = $index;
		$this->type = $type;
	}

	/**
	 * Sets the fields to retrieve from the documents.
	 * @param array $fields the fields to be selected.
	 * @return static the query object itself
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-fields.html
	 */
	public function fields($fields)
	{
		$this->fields = $fields;
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