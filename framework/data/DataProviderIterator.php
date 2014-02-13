<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use yii\base\Component;

/**
 * ArrayDataProviderIterator allows iteration over large data sets without holding the entire set in memory.
 * It iterates over the results of a data provider, starting at the first page
 * of results and ending at the last page. It is usually only suited for use with [[ActiveDataProvider]].
 *
 * For example, the following code will iterate over all registered users (active record class User) without
 * running out of memory, even if there are millions of users in the database.
 * ~~~
 * $query = User::find();
 * $dataProvider = new ActiveDataProvider([
 * 	 'query'=>$query
 * ]);
 * $iterator = new DataProviderIterator($dataProvider);
 * foreach($iterator as $user) {
 *	 echo $user->name."\n";
 * }
 * ~~~
 *
 * @property DataProviderInterface $dataProvider the data provider to iterate over
 * @property integer $totalCount the total number of items in the iterator
 *
 * @author Charles Pick <charles.pick@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class DataProviderIterator extends Component implements \Iterator, \Countable
{
	private $_dataProvider;
	private $_currentIndex=-1;
	private $_currentPage=0;
	private $_totalCount=-1;
	private $_items;

	/**
	 * Constructor.
	 * @param DataProviderInterface $dataProvider the data provider to iterate over
	 * @param integer $pageSize pageSize to use for iteration. This is the number of objects loaded into memory at the same time.
	 */
	public function __construct($dataProvider, $pageSize=null)
	{
		$this->_dataProvider=$dataProvider;
		$this->_totalCount=$dataProvider->getTotalCount();

		if(($pagination=$this->_dataProvider->getPagination())===false)
			$this->_dataProvider->setPagination($pagination=new Pagination());

		if($pageSize!==null)
			$pagination->pageSize = $pageSize;
	}

	/**
	 * Returns the data provider to iterate over
	 * @return DataProvider the data provider to iterate over
	 */
	public function getDataProvider()
	{
		return $this->_dataProvider;
	}

	/**
	 * Gets the total number of items to iterate over
	 * @return integer the total number of items to iterate over
	 */
	public function getTotalCount()
	{
		return $this->_totalCount;
	}

	/**
	 * Loads a page of items
	 * @return array the items from the next page of results
	 */
	protected function loadPage()
	{
		$this->_dataProvider->getPagination()->setPage($this->_currentPage);
		return $this->_items=$this->_dataProvider->getModels();
	}

	/**
	 * Gets the current item in the list.
	 * This method is required by the Iterator interface.
	 * @return mixed the current item in the list
	 */
	public function current()
	{
		return $this->_items[$this->_currentIndex];
	}

	/**
	 * Gets the key of the current item.
	 * This method is required by the Iterator interface.
	 * @return integer the key of the current item
	 */
	public function key()
	{
		$pageSize=$this->_dataProvider->getPagination()->pageSize;
		return $this->_currentPage*$pageSize+$this->_currentIndex;
	}

	/**
	 * Moves the pointer to the next item in the list.
	 * This method is required by the Iterator interface.
	 */
	public function next()
	{
		$pageSize=$this->_dataProvider->getPagination()->pageSize;
		$this->_currentIndex++;
		if($this->_currentIndex >= $pageSize)
		{
			$this->_currentPage++;
			$this->_currentIndex=0;
			$this->loadPage();
		}
	}

	/**
	 * Rewinds the iterator to the start of the list.
	 * This method is required by the Iterator interface.
	 */
	public function rewind()
	{
		$this->_currentIndex=0;
		$this->_currentPage=0;
		$this->loadPage();
	}

	/**
	 * Checks if the current position is valid or not.
	 * This method is required by the Iterator interface.
	 * @return boolean true if this index is valid
	 */
	public function valid()
	{
		return $this->key() < $this->_totalCount;
	}

	/**
	 * Gets the total number of items in the dataProvider.
	 * This method is required by the Countable interface.
	 * @return integer the total number of items
	 */
	public function count()
	{
		return $this->_totalCount;
	}
}