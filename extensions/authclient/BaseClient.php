<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * BaseClient is a base Auth Client class.
 *
 * @see ClientInterface
 *
 * @property string $id Service id.
 * @property string $name Service name.
 * @property array $normalizeUserAttributeMap Normalize user attribute map.
 * @property string $title Service title.
 * @property array $userAttributes List of user attributes.
 * @property array $viewOptions View options in format: optionName => optionValue.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseClient extends Component implements ClientInterface
{
    /**
     * @var string auth service id.
     * This value mainly used as HTTP request parameter.
     */
    private $_id;
    /**
     * @var string auth service name.
     * This value may be used in database records, CSS files and so on.
     */
    private $_name;
    /**
     * @var string auth service title to display in views.
     */
    private $_title;
    /**
     * @var array authenticated user attributes.
     */
    private $_userAttributes;
    /**
     * @var array map used to normalize user attributes fetched from external auth service
     * in format: rawAttributeName => normalizedAttributeName
     */
    private $_normalizeUserAttributeMap;
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
     * @param string $name service name.
     */
    public function setName($name)
    {
        $this->_name = $name;
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
     * @param string $title service title.
     */
    public function setTitle($title)
    {
        $this->_title = $title;
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
     * @param array $userAttributes list of user attributes
     */
    public function setUserAttributes($userAttributes)
    {
        $this->_userAttributes = $this->normalizeUserAttributes($userAttributes);
    }

    /**
     * @return array list of user attributes
     */
    public function getUserAttributes()
    {
        if ($this->_userAttributes === null) {
            $this->_userAttributes = $this->normalizeUserAttributes($this->initUserAttributes());
        }

        return $this->_userAttributes;
    }

    /**
     * @param array $normalizeUserAttributeMap normalize user attribute map.
     */
    public function setNormalizeUserAttributeMap($normalizeUserAttributeMap)
    {
        $this->_normalizeUserAttributeMap = $normalizeUserAttributeMap;
    }

    /**
     * @return array normalize user attribute map.
     */
    public function getNormalizeUserAttributeMap()
    {
        if ($this->_normalizeUserAttributeMap === null) {
            $this->_normalizeUserAttributeMap = $this->defaultNormalizeUserAttributeMap();
        }

        return $this->_normalizeUserAttributeMap;
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
        return Inflector::camel2id(StringHelper::basename(get_class($this)));
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
     * Returns the default [[normalizeUserAttributeMap]] value.
     * Particular client may override this method in order to provide specific default map.
     * @return array normalize attribute map.
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [];
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

    /**
     * Normalize given user attributes according to [[normalizeUserAttributeMap]].
     * @param array $attributes raw attributes.
     * @return array normalized attributes.
     */
    protected function normalizeUserAttributes($attributes)
    {
        foreach ($this->getNormalizeUserAttributeMap() as $normalizedName => $actualName) {
            if (array_key_exists($actualName, $attributes)) {
                $attributes[$normalizedName] = $attributes[$actualName];
            }
        }

        return $attributes;
    }
}
