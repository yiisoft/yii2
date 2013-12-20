<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use Yii;
use yii\base\NotSupportedException;
use yii\helpers\StringHelper;

/**
 * Class ProviderTrait
 *
 * @see ClientInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
trait ClientTrait
{
	/**
	 * @var string service id.
	 * This value mainly used as HTTP request parameter.
	 */
	private $_id;
	/**
	 * @var string service unique name.
	 * This value may be used in database records, CSS files and so on.
	 */
	private $_name;
	/**
	 * @var string service title to display in views.
	 */
	private $_title;
	/**
	 * @var array authenticated user attributes.
	 */
	private $_userAttributes;
	/**
	 * @var array view options in format: optionName => optionValue
	 */
	private $_viewOptions;

	/**
	 * @param string $id service id.
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * @return string service id
	 */
	public function getId()
	{
		if (empty($this->_id)) {
			$this->_id = $this->getName();
		}
		return $this->_id;
	}

	/**
	 * @return string service name.
	 */
	public function getName()
	{
		if ($this->_name === null) {
			$this->_name = $this->defaultName();
		}
		return $this->_name;
	}

	/**
	 * @param string $name service name.
	 */
	public function setName($name)
	{
		$this->_name = $name;
	}

	/**
	 * @return string service title.
	 */
	public function getTitle()
	{
		if ($this->_title === null) {
			$this->_title = $this->defaultTitle();
		}
		return $this->_title;
	}

	/**
	 * @param string $title service title.
	 */
	public function setTitle($title)
	{
		$this->_title = $title;
	}

	/**
	 * @return array list of user attributes
	 */
	public function getUserAttributes()
	{
		if ($this->_userAttributes === null) {
			$this->_userAttributes = $this->initUserAttributes();
		}
		return $this->_userAttributes;
	}

	/**
	 * @param array $userAttributes list of user attributes
	 */
	public function setUserAttributes($userAttributes)
	{
		$this->_userAttributes = $userAttributes;
	}

	/**
	 * @param array $viewOptions view options in format: optionName => optionValue
	 */
	public function setViewOptions($viewOptions)
	{
		$this->_viewOptions = $viewOptions;
	}

	/**
	 * @return array view options in format: optionName => optionValue
	 */
	public function getViewOptions()
	{
		if ($this->_viewOptions === null) {
			$this->_viewOptions = $this->defaultViewOptions();
		}
		return $this->_viewOptions;
	}

	/**
	 * Generates service name.
	 * @return string service name.
	 */
	protected function defaultName()
	{
		return StringHelper::basename(get_class($this));
	}

	/**
	 * Generates service title.
	 * @return string service title.
	 */
	protected function defaultTitle()
	{
		return StringHelper::basename(get_class($this));
	}

	/**
	 * Initializes authenticated user attributes.
	 * @return array auth user attributes.
	 */
	protected function initUserAttributes()
	{
		throw new NotSupportedException('Method "' . get_class($this) . '::' . __FUNCTION__ . '" not implemented.');
	}

	/**
	 * Returns the default [[viewOptions]] value.
	 * Particular client may override this method in order to provide specific default view options.
	 * @return array list of default [[viewOptions]]
	 */
	protected function defaultViewOptions()
	{
		return [];
	}
}