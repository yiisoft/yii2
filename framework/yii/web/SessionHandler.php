<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * SessionHandler is a base class for session handler implementations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class SessionHandler implements \SessionHandlerInterface
{
	/**
	 * @var Session the session component using this handler.
	 */
	public $owner;

	/**
	 * Session open handler.
	 * @param string $savePath session save path
	 * @param string $sessionName session name
	 * @return boolean whether session is opened successfully
	 */
	public function open($savePath, $sessionName)
	{
		return true;
	}

	/**
	 * Session close handler.
	 * @return boolean whether session is closed successfully
	 */
	public function close()
	{
		return true;
	}

	/**
	 * Session read handler.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function read($id)
	{
		return '';
	}

	/**
	 * Session write handler.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function write($id, $data)
	{
		return true;
	}

	/**
	 * Session destroy handler.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroy($id)
	{
		return true;
	}

	/**
	 * Session GC (garbage collection) handler.
	 * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * @return boolean whether session is GCed successfully
	 */
	public function gc($maxLifetime)
	{
		return true;
	}
}
