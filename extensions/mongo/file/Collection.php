<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo\file;

use yii\mongo\Exception;

/**
 * Collection represents the Mongo GridFS collection information.
 *
 * @method \MongoGridFSCursor find() returns a cursor for the search results.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Collection extends \yii\mongo\Collection
{
	/**
	 * @var \MongoGridFS Mongo GridFS collection instance.
	 */
	public $mongoCollection;

	/**
	 * Removes data from the collection.
	 * @param array $condition description of records to remove.
	 * @param array $options list of options in format: optionName => optionValue.
	 * @return integer|boolean number of updated documents or whether operation was successful.
	 * @throws Exception on failure.
	 */
	public function remove($condition = [], $options = [])
	{
		$result = parent::remove($condition, $options);
		$this->tryLastError(); // MongoGridFS::remove will return even if the remove failed
		return $result;
	}

	/**
	 * @param string $filename name of the file to store.
	 * @param array $metadata other metadata fields to include in the file document.
	 * @return mixed the "_id" of the saved file document. This will be a generated [[\MongoId]]
	 * unless an "_id" was explicitly specified in the metadata.
	 */
	public function put($filename, $metadata = [])
	{
		return $this->mongoCollection->put($filename, $metadata);
	}

	/**
	 * @param string $bytes string of bytes to store.
	 * @param array $metadata other metadata fields to include in the file document.
	 * @param array $options list of options in format: optionName => optionValue
	 * @return mixed the "_id" of the saved file document. This will be a generated [[\MongoId]]
	 * unless an "_id" was explicitly specified in the metadata.
	 */
	public function storeBytes($bytes, $metadata = [], $options = [])
	{
		$options = array_merge(['w' => 1], $options);
		return $this->mongoCollection->storeBytes($bytes, $metadata, $options);
	}

	/**
	 * @param string $filename name of the file to store.
	 * @param array $metadata other metadata fields to include in the file document.
	 * @param array $options list of options in format: optionName => optionValue
	 * @return mixed the "_id" of the saved file document. This will be a generated [[\MongoId]]
	 * unless an "_id" was explicitly specified in the metadata.
	 */
	public function storeFile($filename, $metadata = [], $options = [])
	{
		$options = array_merge(['w' => 1], $options);
		return $this->mongoCollection->storeFile($filename, $metadata, $options);
	}

	/**
	 * @param string $name name of the uploaded file to store. This should correspond to
	 * the file field's name attribute in the HTML form.
	 * @param array $metadata other metadata fields to include in the file document.
	 * @return mixed the "_id" of the saved file document. This will be a generated [[\MongoId]]
	 * unless an "_id" was explicitly specified in the metadata.
	 */
	public function storeUploads($name, $metadata = [])
	{
		return $this->mongoCollection->storeUpload($name, $metadata);
	}

	/**
	 * @param mixed $id _id of the file to find.
	 * @return \MongoGridFSFile|null found file, or null if file does not exist
	 */
	public function get($id)
	{
		return $this->mongoCollection->get($id);
	}

	/**
	 * @param mixed $id _id of the file to find.
	 * @return boolean whether the operation was successful.
	 */
	public function delete($id)
	{
		$result = $this->mongoCollection->delete($id);
		$this->tryResultError($result);
		return true;
	}
}