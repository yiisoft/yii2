<?php
/**
 * Request class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Request extends ApplicationComponent
{
	private $_scriptFile;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by preprocessing
	 * the user request data.
	 */
	public function init()
	{
	}

	/**
	 * Returns the relative URL of the entry script.
	 * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
	 * @return string the relative URL of the entry script.
	 */
	public function getScriptUrl()
	{
		if($this->_scriptUrl===null)
		{
			$scriptName=basename($_SERVER['SCRIPT_FILENAME']);
			if(basename($_SERVER['SCRIPT_NAME'])===$scriptName)
				$this->_scriptUrl=$_SERVER['SCRIPT_NAME'];
			else if(basename($_SERVER['PHP_SELF'])===$scriptName)
				$this->_scriptUrl=$_SERVER['PHP_SELF'];
			else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$scriptName)
				$this->_scriptUrl=$_SERVER['ORIG_SCRIPT_NAME'];
			else if(($pos=strpos($_SERVER['PHP_SELF'],'/'.$scriptName))!==false)
				$this->_scriptUrl=substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
			else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT'])===0)
				$this->_scriptUrl=str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
			else
				throw new Exception(Yii::t('yii','CHttpRequest is unable to determine the entry script URL.'));
		}
		return $this->_scriptUrl;
	}

	/**
	 * Sets the relative URL for the application entry script.
	 * This setter is provided in case the entry script URL cannot be determined
	 * on certain Web servers.
	 * @param string $value the relative URL for the application entry script.
	 */
	public function setScriptUrl($value)
	{
		$this->_scriptUrl='/'.trim($value,'/');
	}

	/**
	 * Returns whether this is an AJAX (XMLHttpRequest) request.
	 * @return boolean whether this is an AJAX (XMLHttpRequest) request.
	 */
	public function getIsAjaxRequest()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
	}

	/**
	 * Returns whether this is an Adobe Flash or Adobe Flex request.
	 * @return boolean whether this is an Adobe Flash or Adobe Flex request.
	 * @since 1.1.11
	 */
	public function getIsFlashRequest()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) && (stripos($_SERVER['HTTP_USER_AGENT'],'Shockwave')!==false || stripos($_SERVER['HTTP_USER_AGENT'],'Flash')!==false);
	}

	/**
	 * Returns the server name.
	 * @return string server name
	 */
	public function getServerName()
	{
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * Returns the server port number.
	 * @return integer server port number
	 */
	public function getServerPort()
	{
		return $_SERVER['SERVER_PORT'];
	}

	/**
	 * Returns the URL referrer, null if not present
	 * @return string URL referrer, null if not present
	 */
	public function getUrlReferrer()
	{
		return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:null;
	}

	/**
	 * Returns the user agent, null if not present.
	 * @return string user agent, null if not present
	 */
	public function getUserAgent()
	{
		return isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
	}

	/**
	 * Returns the user IP address.
	 * @return string user IP address
	 */
	public function getUserHostAddress()
	{
		return isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
	}

	/**
	 * Returns the user host name, null if it cannot be determined.
	 * @return string user host name, null if cannot be determined
	 */
	public function getUserHost()
	{
		return isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:null;
	}

	/**
	 * Returns entry script file path.
	 * @return string entry script file path (processed w/ realpath())
	 */
	public function getScriptFile()
	{
		if($this->_scriptFile!==null)
			return $this->_scriptFile;
		else
			return $this->_scriptFile=realpath($_SERVER['SCRIPT_FILENAME']);
	}
}
