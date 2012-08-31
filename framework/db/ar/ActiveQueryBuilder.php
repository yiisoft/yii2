<?php
/**
 * ActiveQueryBuilder class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

/**
 * ActiveQueryBuilder is ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveQueryBuilder extends \yii\base\Object
{
	/**
	 * @var \yii\db\dao\QueryBuilder
	 */
	public $queryBuilder;
	/**
	 * @var ActiveQuery
	 */
	public $query;

	public function __construct($query, $config = array())
	{
		$this->query = $query;
		parent::__construct($config);
	}

	public function build()
	{

	}
}