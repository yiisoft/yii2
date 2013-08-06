<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * Sort represents information relevant to sorting.
 *
 * When data needs to be sorted according to one or several attributes,
 * we can use Sort to represent the sorting information and generate
 * appropriate hyperlinks that can lead to sort actions.
 *
 * A typical usage example is as follows,
 *
 * ~~~
 * function actionIndex()
 * {
 *     $sort = new Sort(array(
 *         'attributes' => array(
 *             'age',
 *             'name' => array(
 *                 'asc' => array('first_name' => Sort::ASC, 'last_name' => Sort::ASC),
 *                 'desc' => array('first_name' => Sort::DESC, 'last_name' => Sort::DESC),
 *                 'default' => Sort::DESC,
 *                 'label' => 'Name',
 *             ),
 *         ),
 *     ));
 *
 *     $models = Article::find()
 *         ->where(array('status' => 1))
 *         ->orderBy($sort->orders)
 *         ->all();
 *
 *     return $this->render('index', array(
 *          'models' => $models,
 *          'sort' => $sort,
 *     ));
 * }
 * ~~~
 *
 * View:
 *
 * ~~~
 * // display links leading to sort actions
 * echo $sort->link('name', 'Name') . ' | ' . $sort->link('age', 'Age');
 *
 * foreach ($models as $model) {
 *     // display $model here
 * }
 * ~~~
 *
 * In the above, we declare two [[attributes]] that support sorting: name and age.
 * We pass the sort information to the Article query so that the query results are
 * sorted by the orders specified by the Sort object. In the view, we show two hyperlinks
 * that can lead to pages with the data sorted by the corresponding attributes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Sort extends Object
{
	/**
	 * Sort ascending
	 */
	const ASC = false;

	/**
	 * Sort descending
	 */
	const DESC = true;

	/**
	 * @var boolean whether the sorting can be applied to multiple attributes simultaneously.
	 * Defaults to false, which means each time the data can only be sorted by one attribute.
	 */
	public $enableMultiSort = false;

	/**
	 * @var array list of attributes that are allowed to be sorted. Its syntax can be
	 * described using the following example:
	 *
	 * ~~~
	 * array(
	 *     'age',
	 *     'name' => array(
	 *         'asc' => array('first_name' => Sort::ASC, 'last_name' => Sort::ASC),
	 *         'desc' => array('first_name' => Sort::DESC, 'last_name' => Sort::DESC),
	 *         'default' => Sort::DESC,
	 *         'label' => 'Name',
	 *     ),
	 * )
	 * ~~~
	 *
	 * In the above, two attributes are declared: "age" and "user". The "age" attribute is
	 * a simple attribute which is equivalent to the following:
	 *
	 * ~~~
	 * 'age' => array(
	 *     'asc' => array('age' => Sort::ASC),
	 *     'desc' => array('age' => Sort::DESC),
	 *     'default' => Sort::ASC,
	 *     'label' => Inflector::camel2words('age'),
	 * )
	 * ~~~
	 *
	 * The "user" attribute is a composite attribute:
	 *
	 * - The "user" key represents the attribute name which will appear in the URLs leading
	 *   to sort actions. Attribute names cannot contain characters listed in [[separators]].
	 * - The "asc" and "desc" elements specify how to sort by the attribute in ascending
	 *   and descending orders, respectively. Their values represent the actual columns and
	 *   the directions by which the data should be sorted by.
	 * - The "default" element specifies by which direction the attribute should be sorted
	 *   if it is not currently sorted (the default value is ascending order).
	 * - The "label" element specifies what label should be used when calling [[link()]] to create
	 *   a sort link. If not set, [[Inflector::camel2words()]] will be called to get a label.
	 *
	 * Note that if the Sort object is already created, you can only use the full format
	 * to configure every attribute. Each attribute must include these elements: asc, desc and label.
	 */
	public $attributes = array();
	/**
	 * @var string the name of the parameter that specifies which attributes to be sorted
	 * in which direction. Defaults to 'sort'.
	 * @see params
	 */
	public $sortVar = 'sort';
	/**
	 * @var string the tag appeared in the [[sortVar]] parameter that indicates the attribute should be sorted
	 * in descending order. Defaults to 'desc'.
	 */
	public $descTag = 'desc';
	/**
	 * @var array the order that should be used when the current request does not specify any order.
	 * The array keys are attribute names and the array values are the corresponding sort directions. For example,
	 *
	 * ~~~
	 * array(
	 *     'name' => Sort::ASC,
	 *     'create_time' => Sort::DESC,
	 * )
	 * ~~~
	 *
	 * @see attributeOrders
	 */
	public $defaultOrder;
	/**
	 * @var string the route of the controller action for displaying the sorted contents.
	 * If not set, it means using the currently requested route.
	 */
	public $route;
	/**
	 * @var array separators used in the generated URL. This must be an array consisting of
	 * two elements. The first element specifies the character separating different
	 * attributes, while the second element specifies the character separating attribute name
	 * and the corresponding sort direction. Defaults to `array('.', '-')`.
	 */
	public $separators = array('.', '-');
	/**
	 * @var array parameters (name => value) that should be used to obtain the current sort directions
	 * and to create new sort URLs. If not set, $_GET will be used instead.
	 *
	 * The array element indexed by [[sortVar]] is considered to be the current sort directions.
	 * If the element does not exist, the [[defaults|default order]] will be used.
	 *
	 * @see sortVar
	 * @see defaultOrder
	 */
	public $params;
	/**
	 * @var \yii\web\UrlManager the URL manager used for creating sort URLs. If not set,
	 * the "urlManager" application component will be used.
	 */
	public $urlManager;

	/**
	 * Normalizes the [[attributes]] property.
	 */
	public function init()
	{
		$attributes = array();
		foreach ($this->attributes as $name => $attribute) {
			if (!is_array($attribute)) {
				$attributes[$attribute] = array(
					'asc' => array($attribute => self::ASC),
					'desc' => array($attribute => self::DESC),
					'label' => Inflector::camel2words($attribute),
				);
			} elseif (!isset($attribute['asc'], $attribute['desc'], $attribute['label'])) {
				$attributes[$name] = array_merge(array(
					'asc' => array($name => self::ASC),
					'desc' => array($name => self::DESC),
					'label' => Inflector::camel2words($name),
				), $attribute);
			}
		}
		$this->attributes = $attributes;
	}

	/**
	 * Returns the columns and their corresponding sort directions.
	 * @param boolean $recalculate whether to recalculate the sort directions
	 * @return array the columns (keys) and their corresponding sort directions (values).
	 * This can be passed to [[\yii\db\Query::orderBy()]] to construct a DB query.
	 */
	public function getOrders($recalculate = false)
	{
		$attributeOrders = $this->getAttributeOrders($recalculate);
		$orders = array();
		foreach ($attributeOrders as $attribute => $direction) {
			$definition = $this->attributes[$attribute];
			$columns = $definition[$direction === self::ASC ? 'asc' : 'desc'];
			foreach ($columns as $name => $dir) {
				$orders[$name] = $dir;
			}
		}
		return $orders;
	}

	private $_attributeOrders;

	/**
	 * Returns the currently requested sort information.
	 * @param boolean $recalculate whether to recalculate the sort directions
	 * @return array sort directions indexed by attribute names.
	 * Sort direction can be either [[Sort::ASC]] for ascending order or
	 * [[Sort::DESC]] for descending order.
	 */
	public function getAttributeOrders($recalculate = false)
	{
		if ($this->_attributeOrders === null || $recalculate) {
			$this->_attributeOrders = array();
			$params = $this->params === null ? $_GET : $this->params;
			if (isset($params[$this->sortVar]) && is_scalar($params[$this->sortVar])) {
				$attributes = explode($this->separators[0], $params[$this->sortVar]);
				foreach ($attributes as $attribute) {
					$descending = false;
					if (($pos = strrpos($attribute, $this->separators[1])) !== false) {
						if ($descending = (substr($attribute, $pos + 1) === $this->descTag)) {
							$attribute = substr($attribute, 0, $pos);
						}
					}

					if (isset($this->attributes[$attribute])) {
						$this->_attributeOrders[$attribute] = $descending;
						if (!$this->enableMultiSort) {
							return $this->_attributeOrders;
						}
					}
				}
			}
			if (empty($this->_attributeOrders) && is_array($this->defaultOrder)) {
				$this->_attributeOrders = $this->defaultOrder;
			}
		}
		return $this->_attributeOrders;
	}

	/**
	 * Returns the sort direction of the specified attribute in the current request.
	 * @param string $attribute the attribute name
	 * @return boolean|null Sort direction of the attribute. Can be either [[Sort::ASC]]
	 * for ascending order or [[Sort::DESC]] for descending order. Null is returned
	 * if the attribute is invalid or does not need to be sorted.
	 */
	public function getAttributeOrder($attribute)
	{
		$orders = $this->getAttributeOrders();
		return isset($orders[$attribute]) ? $orders[$attribute] : null;
	}

	/**
	 * Generates a hyperlink that links to the sort action to sort by the specified attribute.
	 * Based on the sort direction, the CSS class of the generated hyperlink will be appended
	 * with "asc" or "desc".
	 * @param string $attribute the attribute name by which the data should be sorted by.
	 * @param array $options additional HTML attributes for the hyperlink tag
	 * @return string the generated hyperlink
	 * @throws InvalidConfigException if the attribute is unknown
	 */
	public function link($attribute, $options = array())
	{
		if (($direction = $this->getAttributeOrder($attribute)) !== null) {
			$class = $direction ? 'desc' : 'asc';
			if (isset($options['class'])) {
				$options['class'] .= ' ' . $class;
			} else {
				$options['class'] = $class;
			}
		}

		$url = $this->createUrl($attribute);
		$options['data-sort'] = $this->createSortVar($attribute);
		return Html::a($this->attributes[$attribute]['label'], $url, $options);
	}

	/**
	 * Creates a URL for sorting the data by the specified attribute.
	 * This method will consider the current sorting status given by [[attributeOrders]].
	 * For example, if the current page already sorts the data by the specified attribute in ascending order,
	 * then the URL created will lead to a page that sorts the data by the specified attribute in descending order.
	 * @param string $attribute the attribute name
	 * @return string the URL for sorting. False if the attribute is invalid.
	 * @throws InvalidConfigException if the attribute is unknown
	 * @see attributeOrders
	 * @see params
	 */
	public function createUrl($attribute)
	{
		$params = $this->params === null ? $_GET : $this->params;
		$params[$this->sortVar] = $this->createSortVar($attribute);
		$route = $this->route === null ? Yii::$app->controller->getRoute() : $this->route;
		$urlManager = $this->urlManager === null ? Yii::$app->getUrlManager() : $this->urlManager;
		return $urlManager->createUrl($route, $params);
	}

	/**
	 * Creates the sort variable for the specified attribute.
	 * The newly created sort variable can be used to create a URL that will lead to
	 * sorting by the specified attribute.
	 * @param string $attribute the attribute name
	 * @return string the value of the sort variable
	 * @throws InvalidConfigException if the specified attribute is not defined in [[attributes]]
	 */
	public function createSortVar($attribute)
	{
		if (!isset($this->attributes[$attribute])) {
			throw new InvalidConfigException("Unknown attribute: $attribute");
		}
		$definition = $this->attributes[$attribute];
		$directions = $this->getAttributeOrders();
		if (isset($directions[$attribute])) {
			$descending = !$directions[$attribute];
			unset($directions[$attribute]);
		} else {
			$descending = !empty($definition['default']);
		}

		if ($this->enableMultiSort) {
			$directions = array_merge(array($attribute => $descending), $directions);
		} else {
			$directions = array($attribute => $descending);
		}

		$sorts = array();
		foreach ($directions as $attribute => $descending) {
			$sorts[] = $descending ? $attribute . $this->separators[1] . $this->descTag : $attribute;
		}
		return implode($this->separators[0], $sorts);
	}

	/**
	 * Returns a value indicating whether the sort definition supports sorting by the named attribute.
	 * @param string $name the attribute name
	 * @return boolean whether the sort definition supports sorting by the named attribute.
	 */
	public function hasAttribute($name)
	{
		return isset($this->attributes[$name]);
	}
}
