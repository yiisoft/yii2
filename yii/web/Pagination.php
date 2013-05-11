<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;

/**
 * Pagination represents information relevant to pagination of data items.
 *
 * When data needs to be rendered in multiple pages, Pagination can be used to
 * represent information such as [[itemCount|total item count]], [[pageSize|page size]],
 * [[page|current page]], etc. These information can be passed to [[yii\widgets\Pager|pagers]]
 * to render pagination buttons or links.
 *
 * The following example shows how to create a pagination object and feed it
 * to a pager.
 *
 * Controller action:
 *
 * ~~~
 * function actionIndex()
 * {
 *     $query = Article::find()->where(array('status' => 1));
 *     $countQuery = clone $query;
 *     $pages = new Pagination($countQuery->count());
 *     $models = $query->offset($pages->offset)
 *         ->limit($pages->limit)
 *         ->all();
 *
 *     $this->render('index', array(
 *          'models' => $models,
 *          'pages' => $pages,
 *     ));
 * }
 * ~~~
 *
 * View:
 *
 * ~~~
 * foreach ($models as $model) {
 *     // display $model here
 * }
 *
 * // display pagination
 * $this->widget('yii\widgets\LinkPager', array(
 *     'pages' => $pages,
 * ));
 * ~~~
 *
 * @property integer $pageCount Number of pages.
 * @property integer $page The zero-based index of the current page.
 * @property integer $offset The offset of the data. This may be used to set the
 * OFFSET value for a SQL statement for fetching the current page of data.
 * @property integer $limit The limit of the data. This may be used to set the
 * LIMIT value for a SQL statement for fetching the current page of data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Pagination extends \yii\base\Object
{
	/**
	 * @var string name of the parameter storing the current page index. Defaults to 'page'.
	 * @see params
	 */
	public $pageVar = 'page';
	/**
	 * @var boolean whether to always have the page parameter in the URL created by [[createUrl()]].
	 * If false and [[page]] is 0, the page parameter will not be put in the URL.
	 */
	public $forcePageVar = false;
	/**
	 * @var string the route of the controller action for displaying the paged contents.
	 * If not set, it means using the currently requested route.
	 */
	public $route;
	/**
	 * @var array parameters (name=>value) that should be used to obtain the current page number
	 * and to create new pagination URLs. If not set, $_GET will be used instead.
	 *
	 * The array element indexed by [[pageVar]] is considered to be the current page number.
	 * If the element does not exist, the current page number is considered 0.
	 */
	public $params;
	/**
	 * @var boolean whether to check if [[page]] is within valid range.
	 * When this property is true, the value of [[page]] will always be between 0 and ([[pageCount]]-1).
	 * Because [[pageCount]] relies on the correct value of [[itemCount]] which may not be available
	 * in some cases (e.g. MongoDB), you may want to set this property to be false to disable the page
	 * number validation. By doing so, [[page]] will return the value indexed by [[pageVar]] in [[params]].
	 */
	public $validatePage = true;
	/**
	 * @var integer number of items on each page. Defaults to 10.
	 * If it is less than 1, it means the page size is infinite, and thus a single page contains all items.
	 */
	public $pageSize = 10;
	/**
	 * @var integer total number of items.
	 */
	public $itemCount;

	/**
	 * Constructor.
	 * @param integer $itemCount total number of items.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($itemCount, $config = array())
	{
		$this->itemCount = $itemCount;
		parent::__construct($config);
	}

	/**
	 * @return integer number of pages
	 */
	public function getPageCount()
	{
		if ($this->pageSize < 1) {
			return $this->itemCount > 0 ? 1 : 0;
		} else {
			$itemCount = $this->itemCount < 0 ? 0 : (int)$this->itemCount;
			return (int)(($itemCount + $this->pageSize - 1) / $this->pageSize);
		}
	}

	private $_page;

	/**
	 * Returns the zero-based current page number.
	 * @param boolean $recalculate whether to recalculate the current page based on the page size and item count.
	 * @return integer the zero-based current page number.
	 */
	public function getPage($recalculate = false)
	{
		if ($this->_page === null || $recalculate) {
			$params = $this->params === null ? $_GET : $this->params;
			if (isset($params[$this->pageVar]) && is_scalar($params[$this->pageVar])) {
				$this->_page = (int)$params[$this->pageVar] - 1;
				if ($this->validatePage) {
					$pageCount = $this->getPageCount();
					if ($this->_page >= $pageCount) {
						$this->_page = $pageCount - 1;
					}
				}
				if ($this->_page < 0) {
					$this->_page = 0;
				}
			} else {
				$this->_page = 0;
			}
		}
		return $this->_page;
	}

	/**
	 * Sets the current page number.
	 * @param integer $value the zero-based index of the current page.
	 */
	public function setPage($value)
	{
		$this->_page = $value;
	}

	/**
	 * Creates the URL suitable for pagination with the specified page number.
	 * This method is mainly called by pagers when creating URLs used to perform pagination.
	 * @param integer $page the zero-based page number that the URL should point to.
	 * @return string the created URL
	 * @see params
	 * @see forcePageVar
	 */
	public function createUrl($page)
	{
		$params = $this->params === null ? $_GET : $this->params;
		if ($page > 0 || $page >= 0 && $this->forcePageVar) {
			$params[$this->pageVar] = $page + 1;
		} else {
			unset($params[$this->pageVar]);
		}
		$route = $this->route === null ? Yii::$app->controller->route : $this->route;
		return Yii::$app->getUrlManager()->createUrl($route, $params);
	}

	/**
	 * @return integer the offset of the data. This may be used to set the
	 * OFFSET value for a SQL statement for fetching the current page of data.
	 */
	public function getOffset()
	{
		return $this->pageSize < 1 ? 0 : $this->getPage() * $this->pageSize;
	}

	/**
	 * @return integer the limit of the data. This may be used to set the
	 * LIMIT value for a SQL statement for fetching the current page of data.
	 * Note that if the page size is infinite, a value -1 will be returned.
	 */
	public function getLimit()
	{
		return $this->pageSize < 1 ? -1 : $this->pageSize;
	}
}
