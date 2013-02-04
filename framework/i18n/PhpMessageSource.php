<?php
/**
 * PhpMessageSource class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

/**
 * PhpMessageSource represents a message source that stores translated messages in PHP scripts.
 *
 * PhpMessageSource uses PHP files and arrays to keep message translations.
 * <ul>
 * <li>All translations are saved under the {@link basePath} directory.</li>
 * <li>Translations in one language are kept as PHP files under an individual subdirectory
 *   whose name is the same as the language ID. Each PHP file contains messages
 *   belonging to the same category, and the file name is the same as the category name.</li>
 * <li>Within a PHP file, an array of (source, translation) pairs is returned.
 * For example:
 * <pre>
 * return array(
 *     'original message 1' => 'translated message 1',
 *     'original message 2' => 'translated message 2',
 * );
 * </pre>
 * </li>
 * </ul>
 * When {@link cachingDuration} is set as a positive number, message translations will be cached.
 *
 * Messages for an extension class (e.g. a widget, a module) can be specially managed and used.
 * In particular, if a message belongs to an extension whose class name is Xyz, then the message category
 * can be specified in the format of 'Xyz.categoryName'. And the corresponding message file
 * is assumed to be 'BasePath/messages/LanguageID/categoryName.php', where 'BasePath' refers to
 * the directory that contains the extension class file. When using Yii::t() to translate an extension message,
 * the category name should be set as 'Xyz.categoryName'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PhpMessageSource extends MessageSource
{
	const CACHE_KEY_PREFIX='Yii.CPhpMessageSource.';

	/**
	 * @var integer the time in seconds that the messages can remain valid in cache.
	 * Defaults to 0, meaning the caching is disabled.
	 */
	public $cachingDuration=0;
	/**
	 * @var string the ID of the cache application component that is used to cache the messages.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching the messages.
	 */
	public $cacheID='cache';
	/**
	 * @var string the base path for all translated messages. Defaults to null, meaning
	 * the "messages" subdirectory of the application directory (e.g. "protected/messages").
	 */
	public $basePath;
	/**
	 * @var array the message paths for extensions that do not have a base class to use as category prefix.
	 * The format of the array should be:
	 * <pre>
	 * array(
	 *     'ExtensionName' => 'ext.ExtensionName.messages',
	 * )
	 * </pre>
	 * Where the key is the name of the extension and the value is the alias to the path
	 * of the "messages" subdirectory of the extension.
	 * When using Yii::t() to translate an extension message, the category name should be
	 * set as 'ExtensionName.categoryName'.
	 * Defaults to an empty array, meaning no extensions registered.
	 * @since 1.1.13
	 */
	public $extensionPaths=array();

	private $_files=array();

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by preprocessing
	 * the user request data.
	 */
	public function init()
	{
		parent::init();
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.messages');
	}

	/**
	 * Determines the message file name based on the given category and language.
	 * If the category name contains a dot, it will be split into the module class name and the category name.
	 * In this case, the message file will be assumed to be located within the 'messages' subdirectory of
	 * the directory containing the module class file.
	 * Otherwise, the message file is assumed to be under the {@link basePath}.
	 * @param string $category category name
	 * @param string $language language ID
	 * @return string the message file path
	 */
	protected function getMessageFile($category,$language)
	{
		if(!isset($this->_files[$category][$language]))
		{
			if(($pos=strpos($category,'.'))!==false)
			{
				$extensionClass=substr($category,0,$pos);
				$extensionCategory=substr($category,$pos+1);
				// First check if there's an extension registered for this class.
				if(isset($this->extensionPaths[$extensionClass]))
					$this->_files[$category][$language]=Yii::getPathOfAlias($this->extensionPaths[$extensionClass]).DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$extensionCategory.'.php';
				else
				{
					// No extension registered, need to find it.
					$class=new ReflectionClass($extensionClass);
					$this->_files[$category][$language]=dirname($class->getFileName()).DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$extensionCategory.'.php';
				}
			}
			else
				$this->_files[$category][$language]=$this->basePath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$category.'.php';
		}
		return $this->_files[$category][$language];
	}

	/**
	 * Loads the message translation for the specified language and category.
	 * @param string $category the message category
	 * @param string $language the target language
	 * @return array the loaded messages
	 */
	protected function loadMessages($category,$language)
	{
		$messageFile=$this->getMessageFile($category,$language);

		if($this->cachingDuration>0 && $this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$key=self::CACHE_KEY_PREFIX . $messageFile;
			if(($data=$cache->get($key))!==false)
				return unserialize($data);
		}

		if(is_file($messageFile))
		{
			$messages=include($messageFile);
			if(!is_array($messages))
				$messages=array();
			if(isset($cache))
			{
				$dependency=new CFileCacheDependency($messageFile);
				$cache->set($key,serialize($messages),$this->cachingDuration,$dependency);
			}
			return $messages;
		}
		else
			return array();
	}
}