<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\util\StringHelper;
use yii\util\Html;

/**
 * Sort represents information relevant to sorting.
 *
 * When data needs to be sorted according to one or several attributes,
 * we can use Sort to represent the sorting information and generate
 * appropriate hyperlinks that can lead to sort actions.
 *
 * Sort is designed to be used together with {@link CActiveRecord}.
 * When creating a Sort instance, you need to specify {@link modelClass}.
 * You can use Sort to generate hyperlinks by calling {@link link}.
 * You can also use Sort to modify a {@link CDbCriteria} instance by calling {@link applyOrder} so that
 * it can cause the query results to be sorted according to the specified
 * attributes.
 *
 * In order to prevent SQL injection attacks, Sort ensures that only valid model attributes
 * can be sorted. This is determined based on {@link modelClass} and {@link attributes}.
 * When {@link attributes} is not set, all attributes belonging to {@link modelClass}
 * can be sorted. When {@link attributes} is set, only those attributes declared in the property
 * can be sorted.
 *
 * By configuring {@link attributes}, one can perform more complex sorts that may
 * consist of things like compound attributes (e.g. sort based on the combination of
 * first name and last name of users).
 *
 * The property {@link attributes} should be an array of key-value pairs, where the keys
 * represent the attribute names, while the values represent the virtual attribute definitions.
 * For more details, please check the documentation about {@link attributes}.
 *
 *  * Controller action:
 *
 * ~~~
 * function actionIndex()
 * {
 *     $sort = new Sort(array(
 *         'attributes' => Article::attributes(),
 *     ));
 *     $models = Article::find()
 *         ->where(array('status' => 1))
 *         ->orderBy($sort->orderBy)
 *         ->all();
 *
 *     $this->render('index', array(
 *          'models' => $models,
 *          'sort' => $sort,
 *     ));
 * }
 * ~~~
 *
 * View:
 *
 * ~~~
 * foreach($models as $model) {
 *     // display $model here
 * }
 *
 * // display pagination
 * $this->widget('yii\web\widgets\LinkPager', array(
 *     'pages' => $pages,
 * ));
 * ~~~
 *
 * @property string $orderBy The order-by columns represented by this sort object.
 * This can be put in the ORDER BY clause of a SQL statement.
 * @property array $directions Sort directions indexed by attribute names.
 * The sort direction. Can be either Sort::SORT_ASC for ascending order or
 * Sort::SORT_DESC for descending order.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Sort extends \yii\base\Object
{
	/**
	 * Sort ascending
	 */
	const SORT_ASC = false;

	/**
	 * Sort descending
	 */
	const SORT_DESC = true;

	/**
	 * @var boolean whether the sorting can be applied to multiple attributes simultaneously.
	 * Defaults to false, which means each time the data can only be sorted by one attribute.
	 */
	public $enableMultiSort = false;

	/**
	 * @var array list of attributes that are allowed to be sorted.
	 * For example, array('user_id','create_time') would specify that only 'user_id'
	 * and 'create_time' of the model {@link modelClass} can be sorted.
	 * By default, this property is an empty array, which means all attributes in
	 * {@link modelClass} are allowed to be sorted.
	 *
	 * This property can also be used to specify complex sorting. To do so,
	 * a virtual attribute can be declared in terms of a key-value pair in the array.
	 * The key refers to the name of the virtual attribute that may appear in the sort request,
	 * while the value specifies the definition of the virtual attribute.
	 *
	 * In the simple case, a key-value pair can be like <code>'user'=>'user_id'</code>
	 * where 'user' is the name of the virtual attribute while 'user_id' means the virtual
	 * attribute is the 'user_id' attribute in the {@link modelClass}.
	 *
	 * A more flexible way is to specify the key-value pair as
	 * <pre>
	 * 'user'=>array(
	 *     'asc'=>'first_name, last_name',
	 *     'desc'=>'first_name DESC, last_name DESC',
	 *     'label'=>'Name'
	 * )
	 * </pre>
	 * where 'user' is the name of the virtual attribute that specifies the full name of user
	 * (a compound attribute consisting of first name and last name of user). In this case,
	 * we have to use an array to define the virtual attribute with three elements: 'asc',
	 * 'desc' and 'label'.
	 *
	 * The above approach can also be used to declare virtual attributes that consist of relational
	 * attributes. For example,
	 * <pre>
	 * 'price'=>array(
	 *     'asc'=>'item.price',
	 *     'desc'=>'item.price DESC',
	 *     'label'=>'Item Price'
	 * )
	 * </pre>
	 *
	 * Note, the attribute name should not contain '-' or '.' characters because
	 * they are used as {@link separators}.
	 *
	 * Starting from version 1.1.3, an additional option named 'default' can be used in the virtual attribute
	 * declaration. This option specifies whether an attribute should be sorted in ascending or descending
	 * order upon user clicking the corresponding sort hyperlink if it is not currently sorted. The valid
	 * option values include 'asc' (default) and 'desc'. For example,
	 * <pre>
	 * 'price'=>array(
	 *     'asc'=>'item.price',
	 *     'desc'=>'item.price DESC',
	 *     'label'=>'Item Price',
	 *     'default'=>'desc',
	 * )
	 * </pre>
	 *
	 * Also starting from version 1.1.3, you can include a star ('*') element in this property so that
	 * all model attributes are available for sorting, in addition to those virtual attributes. For example,
	 * <pre>
	 * 'attributes'=>array(
	 *     'price'=>array(
	 *         'asc'=>'item.price',
	 *         'desc'=>'item.price DESC',
	 *         'label'=>'Item Price',
	 *         'default'=>'desc',
	 *     ),
	 *     '*',
	 * )
	 * </pre>
	 * Note that when a name appears as both a model attribute and a virtual attribute, the position of
	 * the star element in the array determines which one takes precedence. In particular, if the star
	 * element is the first element in the array, the model attribute takes precedence; and if the star
	 * element is the last one, the virtual attribute takes precedence.
	 */
	public $attributes = array();
	/**
	 * @var string the name of the GET parameter that specifies which attributes to be sorted
	 * in which direction. Defaults to 'sort'.
	 */
	public $sortVar = 'sort';
	/**
	 * @var string the tag appeared in the GET parameter that indicates the attribute should be sorted
	 * in descending order. Defaults to 'desc'.
	 */
	public $descTag = 'desc';
	/**
	 * @var mixed the default order that should be applied to the query criteria when
	 * the current request does not specify any sort. For example, 'name, create_time DESC' or
	 * 'UPPER(name)'.
	 *
	 * Starting from version 1.1.3, you can also specify the default order using an array.
	 * The array keys could be attribute names or virtual attribute names as declared in {@link attributes},
	 * and the array values indicate whether the sorting of the corresponding attributes should
	 * be in descending order. For example,
	 * <pre>
	 * 'defaultOrder'=>array(
	 *     'price'=>Sort::SORT_DESC,
	 * )
	 * </pre>
	 * `SORT_DESC` and `SORT_ASC` are available since 1.1.10. In earlier Yii versions you should use
	 * `true` and `false` respectively.
	 *
	 * Please note when using array to specify the default order, the corresponding attributes
	 * will be put into {@link directions} and thus affect how the sort links are rendered
	 * (e.g. an arrow may be displayed next to the currently active sort link).
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
	 * and the corresponding sort direction. Defaults to `array('-', '.')`.
	 */
	public $separators = array('-', '.');
	/**
	 * @var array parameters (name=>value) that should be used to obtain the current sort directions
	 * and to create new sort URLs. If not set, $_GET will be used instead.
	 *
	 * The array element indexed by [[sortVar]] is considered to be the current sort directions.
	 * If the element does not exist, the [[defaultOrder]] will be used.
	 */
	public $params;

	private $_directions;


	/**
	 * @return string the order-by columns represented by this sort object.
	 * This can be put in the ORDER BY clause of a SQL statement.
	 */
	public function getOrderBy()
	{
		$directions = $this->getDirections();
		if (empty($directions)) {
			return is_string($this->defaultOrder) ? $this->defaultOrder : '';
		} else {
			$orders = array();
			foreach ($directions as $attribute => $descending) {
				$definition = $this->getDefinition($attribute);
				if ($descending) {
					$orders[] = isset($definition['desc']) ? $definition['desc'] : $attribute . ' DESC';
				} else {
					$orders[] = isset($definition['asc']) ? $definition['asc'] : $attribute;
				}
			}
			return implode(', ', $orders);
		}
	}

	/**
	 * Generates a hyperlink that can be clicked to cause sorting.
	 * @param string $attribute the attribute name. This must be the actual attribute name, not alias.
	 * If it is an attribute of a related AR object, the name should be prefixed with
	 * the relation name (e.g. 'author.name', where 'author' is the relation name).
	 * @param string $label the link label. If null, the label will be determined according
	 * to the attribute (see {@link resolveLabel}).
	 * @param array $htmlOptions additional HTML attributes for the hyperlink tag
	 * @return string the generated hyperlink
	 */
	public function link($attribute, $label = null, $htmlOptions = array())
	{
		if (($definition = $this->getDefinition($attribute)) === false) {
			return false;
		}

		if ($label === null) {
			$label = isset($definition['label']) ? $definition['label'] : StringHelper::camel2words($attribute);
		}

		if (($direction = $this->getDirection($attribute)) !== null) {
			$class = $direction ? 'desc' : 'asc';
			if (isset($htmlOptions['class'])) {
				$htmlOptions['class'] .= ' ' . $class;
			} else {
				$htmlOptions['class'] = $class;
			}
		}

		$url = $this->createUrl($attribute);

		return Html::link($label, $url, $htmlOptions);
	}

	/**
	 * Returns the currently requested sort information.
	 * @param boolean $recalculate whether to recalculate the sort directions
	 * @return array sort directions indexed by attribute names.
	 * Sort direction can be either Sort::SORT_ASC for ascending order or
	 * Sort::SORT_DESC for descending order.
	 */
	public function getDirections($recalculate = false)
	{
		if ($this->_directions === null || $recalculate) {
			$this->_directions = array();
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

					if (($this->getDefinition($attribute)) !== false) {
						$this->_directions[$attribute] = $descending;
						if (!$this->enableMultiSort) {
							return $this->_directions;
						}
					}
				}
			}
			if ($this->_directions === array() && is_array($this->defaultOrder)) {
				$this->_directions = $this->defaultOrder;
			}
		}
		return $this->_directions;
	}

	/**
	 * Returns the sort direction of the specified attribute in the current request.
	 * @param string $attribute the attribute name
	 * @return boolean|null Sort direction of the attribute. Can be either Sort::SORT_ASC
	 * for ascending order or Sort::SORT_DESC for descending order. Value is null
	 * if the attribute does not need to be sorted.
	 */
	public function getDirection($attribute)
	{
		$this->getDirections();
		return isset($this->_directions[$attribute]) ? $this->_directions[$attribute] : null;
	}

	/**
	 * Creates a URL for sorting the data by the specified attribute.
	 * This method will consider the current sorting status given by [[directions]].
	 * For example, if the current page already sorts the data by the specified attribute in ascending order,
	 * then the URL created will lead to a page that sorts the data by the specified attribute in descending order.
	 * @param string $attribute the attribute name
	 * @return string|boolean the URL for sorting. False if the attribute is invalid.
	 * @see params
	 */
	public function createUrl($attribute)
	{
		if (($definition = $this->getDefinition($attribute)) === false) {
			return false;
		}
		$directions = $this->getDirections();
		if (isset($directions[$attribute])) {
			$descending = !$directions[$attribute];
			unset($directions[$attribute]);
		} elseif (isset($definition['default'])) {
			$descending = $definition['default'] === 'desc';
		} else {
			$descending = false;
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
		$params = $this->params === null ? $_GET : $this->params;
		$params[$this->sortVar] = implode($this->separators[0], $sorts);
		$route = $this->route === null ? Yii::$app->controller->route : $this->route;

		return Yii::$app->getUrlManager()->createUrl($route, $params);
	}

	/**
	 * Returns the real definition of an attribute given its name.
	 *
	 * The resolution is based on {@link attributes} and {@link CActiveRecord::attributeNames}.
	 * <ul>
	 * <li>When {@link attributes} is an empty array, if the name refers to an attribute of {@link modelClass},
	 * then the name is returned back.</li>
	 * <li>When {@link attributes} is not empty, if the name refers to an attribute declared in {@link attributes},
	 * then the corresponding virtual attribute definition is returned. Starting from version 1.1.3, if {@link attributes}
	 * contains a star ('*') element, the name will also be used to match against all model attributes.</li>
	 * <li>In all other cases, false is returned, meaning the name does not refer to a valid attribute.</li>
	 * </ul>
	 * @param string $attribute the attribute name that the user requests to sort on
	 * @return mixed the attribute name or the virtual attribute definition. False if the attribute cannot be sorted.
	 */
	public function getDefinition($attribute)
	{
		if (isset($this->attributes[$attribute])) {
			return $this->attributes[$attribute];
		} elseif (in_array($attribute, $this->attributes, true)) {
			return array(
				'asc' => $attribute,
				'desc' => "$attribute DESC",
			);
		} else {
			return false;
		}
	}
}