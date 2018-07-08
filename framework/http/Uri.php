<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\UriInterface;
use yii\base\BaseObject;
use yii\base\ErrorHandler;
use yii\base\InvalidArgumentException;

/**
 * Uri represents a URI.
 *
 * Create from components example:
 *
 * ```php
 * $uri = new Uri([
 *     'scheme' => 'http',
 *     'user' => 'username',
 *     'password' => 'password',
 *     'host' => 'example.com',
 *     'port' => 9090,
 *     'path' => '/content/path',
 *     'query' => 'foo=some',
 *     'fragment' => 'anchor',
 * ]);
 * ```
 *
 * Create from string example:
 *
 * ```php
 * $uri = new Uri(['string' => 'http://example.com?foo=some']);
 * ```
 *
 * Create using PSR-7 syntax:
 *
 * ```php
 * $uri = (new Uri())
 *     ->withScheme('http')
 *     ->withUserInfo('username', 'password')
 *     ->withHost('example.com')
 *     ->withPort(9090)
 *     ->withPath('/content/path')
 *     ->withQuery('foo=some')
 *     ->withFragment('anchor');
 * ```
 *
 * @property string $scheme the scheme component of the URI.
 * @property string $user
 * @property string $password
 * @property string $host the hostname to be used.
 * @property int|null $port port number.
 * @property string $path the path component of the URI
 * @property string|array $query the query string or array of query parameters.
 * @property string $fragment URI fragment.
 * @property string $authority the authority component of the URI. This property is read-only.
 * @property string $userInfo the user information component of the URI. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class Uri extends BaseObject implements UriInterface
{
    /**
     * @var string URI complete string.
     */
    private $_string;
    /**
     * @var array URI components.
     */
    private $_components;
    /**
     * @var array scheme default ports in format: `[scheme => port]`
     */
    private static $defaultPorts = [
        'http'  => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];


    /**
     * @return string URI string representation.
     */
    public function getString()
    {
        if ($this->_string !== null) {
            return $this->_string;
        }
        if ($this->_components === null) {
            return '';
        }
        return $this->composeUri($this->_components);
    }

    /**
     * @param string $string URI full string.
     */
    public function setString($string)
    {
        $this->_string = $string;
        $this->_components = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->getComponent('scheme');
    }

    /**
     * Sets up the scheme component of the URI.
     * @param string $scheme the scheme.
     */
    public function setScheme($scheme)
    {
        $this->setComponent('scheme', $scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        if ($this->getScheme() === $scheme) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setScheme($scheme);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        return $this->composeAuthority($this->getComponents());
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return $this->composeUserInfo($this->getComponents());
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->getComponent('host', '');
    }

    /**
     * Specifies hostname.
     * @param string $host the hostname to be used.
     */
    public function setHost($host)
    {
        $this->setComponent('host', $host);
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        if ($this->getHost() === $host) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setHost($host);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->getComponent('port');
    }

    /**
     * Specifies port.
     * @param int|null $port The port to be used; a `null` value removes the port information.
     */
    public function setPort($port)
    {
        if ($port !== null) {
            if (!is_int($port)) {
                throw new InvalidArgumentException('URI port must be an integer.');
            }
        }
        $this->setComponent('port', $port);
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        if ($this->getPort() === $port) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setPort($port);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->getComponent('path', '');
    }

    /**
     * Specifies path component of the URI
     * @param string $path the path to be used.
     */
    public function setPath($path)
    {
        $this->setComponent('path', $path);
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        if ($this->getPath() === $path) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setPath($path);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->getComponent('query', '');
    }

    /**
     * Specifies query string.
     * @param string|array|object $query the query string or array of query parameters.
     */
    public function setQuery($query)
    {
        if (is_array($query) || is_object($query)) {
            $query = http_build_query($query);
        }
        $this->setComponent('query', $query);
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        if ($this->getQuery() === $query) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setQuery($query);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->getComponent('fragment', '');
    }

    /**
     * Specifies URI fragment.
     * @param string $fragment the fragment to be used.
     */
    public function setFragment($fragment)
    {
        $this->setComponent('fragment', $fragment);
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        if ($this->getFragment() === $fragment) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setFragment($fragment);
        return $newInstance;
    }

    /**
     * @return string the user name to use for authority.
     */
    public function getUser()
    {
        return $this->getComponent('user', '');
    }

    /**
     * @param string $user the user name to use for authority.
     */
    public function setUser($user)
    {
        $this->setComponent('user', $user);
    }

    /**
     * @return string password associated with [[user]].
     */
    public function getPassword()
    {
        return $this->getComponent('pass', '');
    }

    /**
     * @param string $password password associated with [[user]].
     */
    public function setPassword($password)
    {
        $this->setComponent('pass', $password);
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $userInfo = $user;
        if ($password != '') {
            $userInfo .= ':' . $password;
        }

        if ($userInfo === $this->composeUserInfo($this->getComponents())) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setUser($user);
        $newInstance->setPassword($password);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            return $this->getString();
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
            return '';
        }
    }

    /**
     * Sets up particular URI component.
     * @param string $name URI component name.
     * @param mixed $value URI component value.
     */
    protected function setComponent($name, $value)
    {
        if ($this->_string !== null) {
            $this->_components = $this->parseUri($this->_string);
        }
        $this->_components[$name] = $value;
        $this->_string = null;
    }

    /**
     * @param string $name URI component name.
     * @param mixed $default default value, which should be returned in case component is not exist.
     * @return mixed URI component value.
     */
    protected function getComponent($name, $default = null)
    {
        $components = $this->getComponents();
        if (isset($components[$name])) {
            return $components[$name];
        }
        return $default;
    }

    /**
     * Returns URI components for this instance as an associative array.
     * @return array URI components in format: `[name => value]`
     */
    protected function getComponents()
    {
        if ($this->_components === null) {
            if ($this->_string === null) {
                return [];
            }
            $this->_components = $this->parseUri($this->_string);
        }
        return $this->_components;
    }

    /**
     * Parses a URI and returns an associative array containing any of the various components of the URI
     * that are present.
     * @param string $uri the URI string to parse.
     * @return array URI components.
     */
    protected function parseUri($uri)
    {
        $components = parse_url($uri);
        if ($components === false) {
            throw new InvalidArgumentException("URI string '{$uri}' is not a valid URI.");
        }
        return $components;
    }

    /**
     * Composes URI string from given components.
     * @param array $components URI components.
     * @return string URI full string.
     */
    protected function composeUri(array $components)
    {
        $uri = '';

        $scheme = empty($components['scheme']) ? '' : $components['scheme'];
        if ($scheme !== '') {
            $uri .= $components['scheme'] . ':';
        }

        $authority = $this->composeAuthority($components);

        if ($authority !== '' || $scheme === 'file') {
            // authority separator is added even when the authority is missing/empty for the "file" scheme
            // while `file:///myfile` and `file:/myfile` are equivalent according to RFC 3986, `file:///` is more common
            // PHP functions and Chrome, for example, use this format
            $uri .= '//' . $authority;
        }

        if (!empty($components['path'])) {
            $uri .= $components['path'];
        }

        if (!empty($components['query'])) {
            $uri .= '?' . $components['query'];
        }

        if (!empty($components['fragment'])) {
            $uri .= '#' . $components['fragment'];
        }

        return $uri;
    }

    /**
     * @param array $components URI components.
     * @return string user info string.
     */
    protected function composeUserInfo(array $components)
    {
        $userInfo = '';
        if (!empty($components['user'])) {
            $userInfo .= $components['user'];
        }
        if (!empty($components['pass'])) {
            $userInfo .= ':' . $components['pass'];
        }
        return $userInfo;
    }

    /**
     * @param array $components URI components.
     * @return string authority string.
     */
    protected function composeAuthority(array $components)
    {
        $authority = '';

        $scheme = empty($components['scheme']) ? '' : $components['scheme'];

        if (empty($components['host'])) {
            if (in_array($scheme, ['http', 'https'], true)) {
                $authority = 'localhost';
            }
        } else {
            $authority = $components['host'];
        }
        if (!empty($components['port']) && !$this->isDefaultPort($scheme, $components['port'])) {
            $authority .= ':' . $components['port'];
        }

        $userInfo = $this->composeUserInfo($components);
        if ($userInfo !== '') {
            $authority = $userInfo . '@' . $authority;
        }

        return $authority;
    }

    /**
     * Checks whether specified port is default one for the specified scheme.
     * @param string $scheme scheme.
     * @param int $port port number.
     * @return bool whether specified port is default for specified scheme
     */
    protected function isDefaultPort($scheme, $port)
    {
        if (!isset(self::$defaultPorts[$scheme])) {
            return false;
        }
        return self::$defaultPorts[$scheme] == $port;
    }
}