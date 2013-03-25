<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\caching\Cache;
use yii\caching\Dependency;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FragmentCache extends Widget
{
	/**
	 * @var string the ID of the cache application component. Defaults to 'cache' (the primary cache application component.)
	 */
	public $cacheID = 'cache';
	/**
	 * @var integer number of seconds that the data can remain valid in cache.
	 * Use 0 to indicate that the cached data will never expire.
	 */
	public $duration = 60;
	/**
	 * @var array|Dependency the dependency that the cached content depends on.
	 * This can be either a [[Dependency]] object or a configuration array for creating the dependency object.
	 * For example,
	 *
	 * ~~~
	 * array(
	 *     'class' => 'yii\caching\DbDependency',
	 *     'sql' => 'SELECT MAX(lastModified) FROM Post',
	 * )
	 * ~~~
	 *
	 * would make the output cache depends on the last modified time of all posts.
	 * If any post has its modification time changed, the cached content would be invalidated.
	 */
	public $dependency;
	/**
	 * @var array list of factors that would cause the variation of the content being cached.
	 * Each factor is a string representing a variation (e.g. the language, a GET parameter).
	 * The following variation setting will cause the content to be cached in different versions
	 * according to the current application language:
	 *
	 * ~~~
	 * array(
	 *     Yii::$app->language,
	 * )
	 */
	public $variations;
	/**
	 * @var boolean whether to enable the fragment cache. You may use this property to turn on and off
	 * the fragment cache according to specific setting (e.g. enable fragment cache only for GET requests).
	 */
	public $enabled = true;
	/**
	 * @var \yii\base\View the view object within which this widget is sued. If not set,
	 * the view registered with the application will be used. This is mainly used by dynamic content feature.
	 */
	public $view;
	/**
	 * @var array
	 */
	public $dynamicPlaceholders;


	/**
	 * Marks the start of content to be cached.
	 * Content displayed after this method call and before {@link endCache()}
	 * will be captured and saved in cache.
	 * This method does nothing if valid content is already found in cache.
	 */
	public function init()
	{
		if ($this->view === null) {
			$this->view = Yii::$app->getView();
		}
		if ($this->getCachedContent() === false && $this->getCache() !== null) {
			array_push($this->view->cachingStack, $this);
			ob_start();
			ob_implicit_flush(false);
		}
	}

	/**
	 * Marks the end of content to be cached.
	 * Content displayed before this method call and after {@link init()}
	 * will be captured and saved in cache.
	 * This method does nothing if valid content is already found in cache.
	 */
	public function run()
	{
		if (($content = $this->getCachedContent()) !== false) {
			echo $content;
		} elseif (($cache = $this->getCache()) !== false) {
			$content = ob_get_clean();
			array_pop($this->view->cachingStack);
			if (is_array($this->dependency)) {
				$this->dependency = Yii::createObject($this->dependency);
			}
			$data = array($content, $this->dynamicPlaceholders);
			$cache->set($this->calculateKey(), $data, $this->duration, $this->dependency);
			echo $content;
		}
	}

	/**
	 * @var string|boolean the cached content. False if the content is not cached.
	 */
	private $_content;

	/**
	 * Returns the cached content if available.
	 * @return string|boolean the cached content. False is returned if valid content is not found in the cache.
	 */
	public function getCachedContent()
	{
		if ($this->_content === null) {
			$this->_content = false;
			if (($cache = $this->getCache()) !== null) {
				$key = $this->calculateKey();
				$data = $cache->get($key);
				if (is_array($data) && count($data) === 2) {
					list ($content, $placeholders) = $data;
					if (is_array($placeholders) && count($placeholders) > 0) {
						foreach ($placeholders as $name => $statements) {
							$placeholders[$name] = $this->view->evaluateDynamicContent($statements);
						}
						$content = strtr($content, $placeholders);
					}
					$this->_content = $content;
				}
			}
		}
		return $this->_content;
	}

	/**
	 * Generates a unique key used for storing the content in cache.
	 * The key generated depends on both [[id]] and [[variations]].
	 * @return string a valid cache key
	 */
	protected function calculateKey()
	{
		$factors = array(__CLASS__, $this->getId());
		if (is_array($this->variations)) {
			foreach ($this->variations as $factor) {
				$factors[] = $factor;
			}
		}
		return $this->getCache()->buildKey($factors);
	}

	/**
	 * @var Cache
	 */
	private $_cache;

	/**
	 * Returns the cache instance used for storing content.
	 * @return Cache the cache instance. Null is returned if the cache component is not available
	 * or [[enabled]] is false.
	 * @throws InvalidConfigException if [[cacheID]] does not point to a valid application component.
	 */
	public function getCache()
	{
		if (!$this->enabled) {
			return null;
		}
		if ($this->_cache === null) {
			$cache = Yii::$app->getComponent($this->cacheID);
			if ($cache instanceof Cache) {
				$this->_cache = $cache;
			} else {
				throw new InvalidConfigException('FragmentCache::cacheID must refer to the ID of a cache application component.');
			}
		}
		return $this->_cache;
	}

	/**
	 * Sets the cache instance used by the session component.
	 * @param Cache $value the cache instance
	 */
	public function setCache($value)
	{
		$this->_cache = $value;
	}
}