<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use yii\base\Exception;
use yii\base\NotSupportedException;
use Yii;

/**
 * Class Client
 *
 * @see http://openid.net/
 *
 * @property string $returnUrl authentication return URL.
 * @property mixed $identity ???
 * @property string $trustRoot client trust root (realm), by default [[\yii\web\Request::hostInfo]] value will be used.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OpenId extends BaseClient implements ClientInterface
{
	/**
	 * @var array list of attributes, which always should be returned from server.
	 */
	public $requiredAttributes = [];
	/**
	 * @var array list of attributes, which could be returned from server.
	 */
	public $optionalAttributes = [];

	/**
	 * @var boolean whether to verify the peer's certificate.
	 */
	public $verifyPeer;
	/**
	 * @var string directory that holds multiple CA certificates.
	 * This value will take effect only if [[verifyPeer]] is set.
	 */
	public $capath;
	/**
	 * @var string the name of a file holding one or more certificates to verify the peer with.
	 * This value will take effect only if [[verifyPeer]] is set.
	 */
	public $cainfo;

	/**
	 * @var string authentication return URL.
	 */
	private $_returnUrl;

	private $_identity;

	private $_claimedId;
	/**
	 * @var string client trust root (realm), by default [[\yii\web\Request::hostInfo]] value will be used.
	 */
	private $_trustRoot;
	/**
	 * @var array data, which should be used to retrieve the OpenID response.
	 * If not set combination of GET and POST will be used.
	 */
	public $data;
	/**
	 * @var array map of matches between AX and SREG attribute names in format: axAttributeName => sregAttributeName
	 */
	public $axToSregMap = [
		'namePerson/friendly' => 'nickname',
		'contact/email' => 'email',
		'namePerson' => 'fullname',
		'birthDate' => 'dob',
		'person/gender' => 'gender',
		'contact/postalCode/home' => 'postcode',
		'contact/country/home' => 'country',
		'pref/language' => 'language',
		'pref/timezone' => 'timezone',
	];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if ($this->data === null) {
			$this->data = array_merge($_GET, $_POST); // OPs may send data as POST or GET.
		}
	}

	public function setIdentity($value)
	{
		if (strlen($value = trim((String) $value))) {
			if (preg_match('#^xri:/*#i', $value, $m)) {
				$value = substr($value, strlen($m[0]));
			} elseif (!preg_match('/^(?:[=@+\$!\(]|https?:)/i', $value)) {
				$value = "http://$value";
			}
			if (preg_match('#^https?://[^/]+$#i', $value, $m)) {
				$value .= '/';
			}
		}
		$this->_identity = $value;
		$this->_claimedId = $value;
	}

	public function getIdentity()
	{
		/* We return claimed_id instead of identity,
		because the developer should see the claimed identifier,
		i.e. what he set as identity, not the op-local identifier (which is what we verify)*/
		return $this->_claimedId;
	}

	/**
	 * @param string $returnUrl authentication return URL.
	 */
	public function setReturnUrl($returnUrl)
	{
		$this->_returnUrl = $returnUrl;
	}

	/**
	 * @return string authentication return URL.
	 */
	public function getReturnUrl()
	{
		if ($this->_returnUrl === null) {
			$this->_returnUrl = $this->defaultReturnUrl();
		}
		return $this->_returnUrl;
	}

	/**
	 * @param string $value client trust root (realm).
	 */
	public function setTrustRoot($value)
	{
		$this->_trustRoot = $value;
	}

	/**
	 * @return string client trust root (realm).
	 */
	public function getTrustRoot()
	{
		if ($this->_trustRoot === null) {
			$this->_trustRoot = Yii::$app->getRequest()->getHostInfo();
		}
		return $this->_trustRoot;
	}

	/**
	 * Generates default [[returnUrl]] value.
	 * @return string default authentication return URL.
	 */
	protected function defaultReturnUrl()
	{
		$params = $_GET;
		foreach ($params as $name => $value) {
			if (strncmp('openid', $name, 6) === 0) {
				unset($params[$name]);
			}
		}
		$url = Yii::$app->getUrlManager()->createUrl(Yii::$app->requestedRoute, $params);
		return $this->getTrustRoot() . $url;
	}

	/**
	 * Checks if the server specified in the url exists.
	 * @param string $url URL to check
	 * @return boolean true, if the server exists; false otherwise
	 */
	public function hostExists($url)
	{
		if (strpos($url, '/') === false) {
			$server = $url;
		} else {
			$server = @parse_url($url, PHP_URL_HOST);
		}
		if (!$server) {
			return false;
		}
		$ips = gethostbynamel($server);
		return !empty($ips);
	}

	/**
	 * Sends HTTP request.
	 * @param string $url request URL.
	 * @param string $method request method.
	 * @param array $params request params.
	 * @return array|string response.
	 * @throws \yii\base\Exception on failure.
	 */
	protected function sendCurlRequest($url, $method = 'GET', $params = [])
	{
		$params = http_build_query($params, '', '&');
		$curl = curl_init($url . ($method == 'GET' && $params ? '?' . $params : ''));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/xrds+xml, */*'));

		if ($this->verifyPeer !== null) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verifyPeer);
			if($this->capath) {
				curl_setopt($curl, CURLOPT_CAPATH, $this->capath);
			}
			if($this->cainfo) {
				curl_setopt($curl, CURLOPT_CAINFO, $this->cainfo);
			}
		}

		if ($method == 'POST') {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		} elseif ($method == 'HEAD') {
			curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_NOBODY, true);
		} else {
			curl_setopt($curl, CURLOPT_HTTPGET, true);
		}
		$response = curl_exec($curl);

		if ($method == 'HEAD') {
			$headers = [];
			foreach (explode("\n", $response) as $header) {
				$pos = strpos($header, ':');
				$name = strtolower(trim(substr($header, 0, $pos)));
				$headers[$name] = trim(substr($header, $pos+1));
			}

			// Updating claimed_id in case of redirections.
			$effectiveUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
			if ($effectiveUrl != $url) {
				$this->_identity = $this->_claimedId = $effectiveUrl;
			}

			return $headers;
		}

		if (curl_errno($curl)) {
			throw new Exception(curl_error($curl), curl_errno($curl));
		}

		return $response;
	}

	/**
	 * Sends HTTP request.
	 * @param string $url request URL.
	 * @param string $method request method.
	 * @param array $params request params.
	 * @return array|string response.
	 * @throws \yii\base\Exception on failure.
	 * @throws \yii\base\NotSupportedException if request method is not supported.
	 */
	protected function sendStreamRequest($url, $method = 'GET', $params = [])
	{
		if (!$this->hostExists($url)) {
			throw new Exception('Invalid request.');
		}

		$params = http_build_query($params, '', '&');
		switch ($method) {
			case 'GET':
				$options = [
					'http' => [
						'method' => 'GET',
						'header' => 'Accept: application/xrds+xml, */*',
						'ignore_errors' => true,
					]
				];
				$url = $url . ($params ? '?' . $params : '');
				break;
			case 'POST':
				$options = [
					'http' => [
						'method' => 'POST',
						'header'  => 'Content-type: application/x-www-form-urlencoded',
						'content' => $params,
						'ignore_errors' => true,
					]
				];
				break;
			case 'HEAD':
				/* We want to send a HEAD request,
				but since get_headers doesn't accept $context parameter,
				we have to change the defaults.*/
				$default = stream_context_get_options(stream_context_get_default());
				stream_context_get_default([
					'http' => [
						'method' => 'HEAD',
						'header' => 'Accept: application/xrds+xml, */*',
						'ignore_errors' => true,
					]
				]);

				$url = $url . ($params ? '?' . $params : '');
				$headersTmp = get_headers($url);
				if (empty($headersTmp)) {
					return [];
				}

				// Parsing headers.
				$headers = [];
				foreach ($headersTmp as $header) {
					$pos = strpos($header, ':');
					$name = strtolower(trim(substr($header, 0, $pos)));
					$headers[$name] = trim(substr($header, $pos + 1));

					/* Following possible redirections. The point is just to have
					claimed_id change with them, because get_headers() will
					follow redirections automatically.
					We ignore redirections with relative paths.
					If any known provider uses them, file a bug report.*/
					if ($name == 'location') {
						if (strpos($headers[$name], 'http') === 0) {
							$this->_identity = $this->_claimedId = $headers[$name];
						} elseif($headers[$name][0] == '/') {
							$parsedUrl = parse_url($this->_claimedId);
							$this->_identity =
							$this->_claimedId = $parsedUrl['scheme'] . '://'
								. $parsedUrl['host']
								. $headers[$name];
						}
					}
				}

				// and restore them
				stream_context_get_default($default);
				return $headers;
			default:
				throw new NotSupportedException("Method {$method} not supported");
		}

		if ($this->verifyPeer) {
			$options = array_merge(
				$options,
				[
					'ssl' => [
						'verify_peer' => true,
						'capath' => $this->capath,
						'cafile' => $this->cainfo,
					]
				]
			);
		}

		$context = stream_context_create($options);
		return file_get_contents($url, false, $context);
	}

	/**
	 * Sends request to the server
	 * @param string $url request URL.
	 * @param string $method request method.
	 * @param array $params request parameters.
	 * @return array|string response.
	 */
	protected function sendRequest($url, $method = 'GET', $params = [])
	{
		if (function_exists('curl_init') && !ini_get('safe_mode')) {
			return $this->sendCurlRequest($url, $method, $params);
		}
		return $this->sendStreamRequest($url, $method, $params);
	}

	protected function buildUrl($url, $parts)
	{
		if (isset($url['query'], $parts['query'])) {
			$parts['query'] = $url['query'] . '&' . $parts['query'];
		}

		$url = $parts + $url;
		$url = $url['scheme'] . '://'
			. (empty($url['username']) ? ''
				:(empty($url['password']) ? "{$url['username']}@"
					:"{$url['username']}:{$url['password']}@"))
			. $url['host']
			. (empty($url['port']) ? '' : ":{$url['port']}")
			. (empty($url['path']) ? '' : $url['path'])
			. (empty($url['query']) ? '' : "?{$url['query']}")
			. (empty($url['fragment']) ? '' : "#{$url['fragment']}");
		return $url;
	}

	/**
	 * Helper function used to scan for <meta>/<link> tags and extract information
	 * from them
	 */
	protected function extractHtmlTagValue($content, $tag, $attrName, $attrValue, $valueName)
	{
		preg_match_all("#<{$tag}[^>]*$attrName=['\"].*?$attrValue.*?['\"][^>]*$valueName=['\"](.+?)['\"][^>]*/?>#i", $content, $matches1);
		preg_match_all("#<{$tag}[^>]*$valueName=['\"](.+?)['\"][^>]*$attrName=['\"].*?$attrValue.*?['\"][^>]*/?>#i", $content, $matches2);
		$result = array_merge($matches1[1], $matches2[1]);
		return empty($result) ? false : $result[0];
	}

	/**
	 * Performs Yadis and HTML discovery.
	 * @param string $url Identity URL.
	 * @return array OpenID provider info, following keys will be available:
	 * - 'url' - string OP Endpoint (i.e. OpenID provider address).
	 * - 'version' - integer OpenID protocol version used by provider.
	 * - 'identity' - string identity value.
	 * - 'identifier_select' - boolean whether to request OP to select identity for an user in OpenID 2, does not affect OpenID 1.
	 * - 'ax' - boolean whether AX attributes should be used.
	 * - 'sreg' - boolean whether SREG attributes should be used.
	 * @throws Exception on failure.
	 */
	public function discover($url)
	{
		if (empty($url)) {
			throw new Exception('No identity supplied.');
		}
		$result = [
			'url' => null,
			'version' => null,
			'identity' => $url,
			'identifier_select' => false,
			'ax' => false,
			'sreg' => false,
		];

		// Use xri.net proxy to resolve i-name identities
		if (!preg_match('#^https?:#', $url)) {
			$url = 'https://xri.net/' . $url;
		}

		/* We save the original url in case of Yadis discovery failure.
		It can happen when we'll be lead to an XRDS document
		which does not have any OpenID2 services.*/
		$originalUrl = $url;

		// A flag to disable yadis discovery in case of failure in headers.
		$yadis = true;

		// We'll jump a maximum of 5 times, to avoid endless redirections.
		for ($i = 0; $i < 5; $i ++) {
			if ($yadis) {
				$headers = $this->sendRequest($url, 'HEAD');

				$next = false;
				if (isset($headers['x-xrds-location'])) {
					$url = $this->buildUrl(parse_url($url), parse_url(trim($headers['x-xrds-location'])));
					$next = true;
				}

				if (isset($headers['content-type'])
					&& (strpos($headers['content-type'], 'application/xrds+xml') !== false
						|| strpos($headers['content-type'], 'text/xml') !== false)
				) {
					/* Apparently, some providers return XRDS documents as text/html.
					While it is against the spec, allowing this here shouldn't break
					compatibility with anything.
					---
					Found an XRDS document, now let's find the server, and optionally delegate.*/
					$content = $this->sendRequest($url, 'GET');

					preg_match_all('#<Service.*?>(.*?)</Service>#s', $content, $m);
					foreach ($m[1] as $content) {
						$content = ' ' . $content; // The space is added, so that strpos doesn't return 0.

						// OpenID 2
						$ns = preg_quote('http://specs.openid.net/auth/2.0/');
						if (preg_match('#<Type>\s*'.$ns.'(server|signon)\s*</Type>#s', $content, $type)) {
							if ($type[1] == 'server') {
								$result['identifier_select'] = true;
							}

							preg_match('#<URI.*?>(.*)</URI>#', $content, $server);
							preg_match('#<(Local|Canonical)ID>(.*)</\1ID>#', $content, $delegate);
							if (empty($server)) {
								throw new Exception('No servers found!');
							}
							// Does the server advertise support for either AX or SREG?
							$result['ax'] = (bool) strpos($content, '<Type>http://openid.net/srv/ax/1.0</Type>');
							$result['sreg'] = strpos($content, '<Type>http://openid.net/sreg/1.0</Type>') || strpos($content, '<Type>http://openid.net/extensions/sreg/1.1</Type>');

							$server = $server[1];
							if (isset($delegate[2])) {
								$result['identity'] = trim($delegate[2]);
							}

							$result['url'] = $server;
							$result['version'] = 2;
							return $result;
						}

						// OpenID 1.1
						$ns = preg_quote('http://openid.net/signon/1.1');
						if (preg_match('#<Type>\s*'.$ns.'\s*</Type>#s', $content)) {
							preg_match('#<URI.*?>(.*)</URI>#', $content, $server);
							preg_match('#<.*?Delegate>(.*)</.*?Delegate>#', $content, $delegate);
							if (empty($server)) {
								throw new Exception('No servers found!');
							}
							// AX can be used only with OpenID 2.0, so checking only SREG
							$result['sreg'] = strpos($content, '<Type>http://openid.net/sreg/1.0</Type>') || strpos($content, '<Type>http://openid.net/extensions/sreg/1.1</Type>');

							$server = $server[1];
							if (isset($delegate[1])) {
								$result['identity'] = $delegate[1];
							}

							$result['url'] = $server;
							$result['version'] = 1;
							return $result;
						}
					}

					$next = true;
					$yadis = false;
					$url = $originalUrl;
					$content = null;
					break;
				}
				if ($next) {
					continue;
				}

				// There are no relevant information in headers, so we search the body.
				$content = $this->sendRequest($url, 'GET');
				$location = $this->extractHtmlTagValue($content, 'meta', 'http-equiv', 'X-XRDS-Location', 'content');
				if ($location) {
					$url = $this->buildUrl(parse_url($url), parse_url($location));
					continue;
				}
			}

			if (!isset($content)) {
				$content = $this->sendRequest($url, 'GET');
			}

			// At this point, the YADIS Discovery has failed, so we'll switch to openid2 HTML discovery, then fallback to openid 1.1 discovery.
			$server = $this->extractHtmlTagValue($content, 'link', 'rel', 'openid2.provider', 'href');
			if (!$server) {
				// The same with openid 1.1
				$server = $this->extractHtmlTagValue($content, 'link', 'rel', 'openid.server', 'href');
				$delegate = $this->extractHtmlTagValue($content, 'link', 'rel', 'openid.delegate', 'href');
				$version = 1;
			} else {
				$delegate = $this->extractHtmlTagValue($content, 'link', 'rel', 'openid2.local_id', 'href');
				$version = 2;
			}

			if ($server) {
				// We found an OpenID2 OP Endpoint
				if ($delegate) {
					// We have also found an OP-Local ID.
					$result['identity'] = $delegate;
				}
				$result['url'] = $server;
				$result['version'] = $version;
				return $result;
			}
			throw new Exception('No servers found!');
		}
		throw new Exception('Endless redirection!');
	}

	/**
	 * Composes SREG request parameters.
	 * @return array SREG parameters.
	 */
	protected function buildSregParams()
	{
		$params = [];
		/* We always use SREG 1.1, even if the server is advertising only support for 1.0.
		That's because it's fully backwards compatibile with 1.0, and some providers
		advertise 1.0 even if they accept only 1.1. One such provider is myopenid.com */
		$params['openid.ns.sreg'] = 'http://openid.net/extensions/sreg/1.1';
		if (!empty($this->requiredAttributes)) {
			$params['openid.sreg.required'] = [];
			foreach ($this->requiredAttributes as $required) {
				if (!isset($this->axToSregMap[$required])) {
					continue;
				}
				$params['openid.sreg.required'][] = $this->axToSregMap[$required];
			}
			$params['openid.sreg.required'] = implode(',', $params['openid.sreg.required']);
		}

		if (!empty($this->optionalAttributes)) {
			$params['openid.sreg.optional'] = [];
			foreach ($this->optionalAttributes as $optional) {
				if (!isset($this->axToSregMap[$optional])) {
					continue;
				}
				$params['openid.sreg.optional'][] = $this->axToSregMap[$optional];
			}
			$params['openid.sreg.optional'] = implode(',', $params['openid.sreg.optional']);
		}
		return $params;
	}

	/**
	 * Composes AX request parameters.
	 * @return array AX parameters.
	 */
	protected function buildAxParams()
	{
		$params = [];
		if (!empty($this->requiredAttributes) || !empty($this->optionalAttributes)) {
			$params['openid.ns.ax'] = 'http://openid.net/srv/ax/1.0';
			$params['openid.ax.mode'] = 'fetch_request';
			$aliases = [];
			$counts = [];
			$required = [];
			$optional = [];
			foreach (['requiredAttributes', 'optionalAttributes'] as $type) {
				foreach ($this->$type as $alias => $field) {
					if (is_int($alias)) {
						$alias = strtr($field, '/', '_');
					}
					$aliases[$alias] = 'http://axschema.org/' . $field;
					if (empty($counts[$alias])) {
						$counts[$alias] = 0;
					}
					$counts[$alias] += 1;
					${$type}[] = $alias;
				}
			}
			foreach ($aliases as $alias => $ns) {
				$params['openid.ax.type.' . $alias] = $ns;
			}
			foreach ($counts as $alias => $count) {
				if ($count == 1) {
					continue;
				}
				$params['openid.ax.count.' . $alias] = $count;
			}

			// Don't send empty ax.requied and ax.if_available.
			// Google and possibly other providers refuse to support ax when one of these is empty.
			if ($required) {
				$params['openid.ax.required'] = implode(',', $required);
			}
			if ($optional) {
				$params['openid.ax.if_available'] = implode(',', $optional);
			}
		}
		return $params;
	}

	/**
	 * Builds authentication URL for the protocol version 1.
	 * @param array $serverInfo OpenID server info.
	 * @return string authentication URL.
	 */
	protected function buildAuthUrlV1($serverInfo)
	{
		$returnUrl = $this->getReturnUrl();
		/* If we have an openid.delegate that is different from our claimed id,
		we need to somehow preserve the claimed id between requests.
		The simplest way is to just send it along with the return_to url.*/
		if ($serverInfo['identity'] != $this->_claimedId) {
			$returnUrl .= (strpos($returnUrl, '?') ? '&' : '?') . 'openid.claimed_id=' . $this->_claimedId;
		}

		$params = array_merge(
			$this->buildSregParams(),
			[
				'openid.return_to' => $returnUrl,
				'openid.mode' => 'checkid_setup',
				'openid.identity' => $serverInfo['identity'],
				'openid.trust_root' => $this->trustRoot,
			]
		);

		return $this->buildUrl(parse_url($serverInfo['url']), ['query' => http_build_query($params, '', '&')]);
	}

	/**
	 * Builds authentication URL for the protocol version 2.
	 * @param array $serverInfo OpenID server info.
	 * @return string authentication URL.
	 */
	protected function buildAuthUrlV2($serverInfo)
	{
		$params = [
			'openid.ns' => 'http://specs.openid.net/auth/2.0',
			'openid.mode' => 'checkid_setup',
			'openid.return_to' => $this->getReturnUrl(),
			'openid.realm' => $this->getTrustRoot(),
		];
		if ($serverInfo['ax']) {
			$params = array_merge($this->buildAxParams(), $params);
		}
		if ($serverInfo['sreg']) {
			$params = array_merge($this->buildSregParams(), $params);
		}
		if (!$serverInfo['ax'] && !$serverInfo['sreg']) {
			// If OP doesn't advertise either SREG, nor AX, let's send them both in worst case we don't get anything in return.
			$params = array_merge($this->buildSregParams(), $this->buildAxParams(), $params);
		}

		if ($serverInfo['identifier_select']) {
			$url = 'http://specs.openid.net/auth/2.0/identifier_select';
			$params['openid.identity'] = $url;
			$params['openid.claimed_id']= $url;
		} else {
			$params['openid.identity'] = $serverInfo['identity'];
			$params['openid.claimed_id'] = $this->_claimedId;
		}
		return $this->buildUrl(parse_url($serverInfo['url']), ['query' => http_build_query($params, '', '&')]);
	}

	/**
	 * Returns authentication URL. Usually, you want to redirect your user to it.
	 * @param boolean $identifierSelect whether to request OP to select identity for an user in OpenID 2, does not affect OpenID 1.
	 * @return string the authentication URL.
	 * @throws Exception on failure.
	 */
	public function buildAuthUrl($identifierSelect = null)
	{
		$serverInfo = $this->discover($this->_identity);
		if ($serverInfo['version'] == 2) {
			if ($identifierSelect !== null) {
				$serverInfo['identifier_select'] = $identifierSelect;
			}
			return $this->buildAuthUrlV2($serverInfo);
		}
		return $this->buildAuthUrlV1($serverInfo);
	}

	/**
	 * Performs OpenID verification with the OP.
	 * @return boolean whether the verification was successful.
	 * @throws Exception
	 */
	public function validate()
	{
		$this->_claimedId = isset($this->data['openid_claimed_id']) ? $this->data['openid_claimed_id'] : $this->data['openid_identity'];
		$params = [
			'openid.assoc_handle' => $this->data['openid_assoc_handle'],
			'openid.signed' => $this->data['openid_signed'],
			'openid.sig' => $this->data['openid_sig'],
		];

		if (isset($this->data['openid_ns'])) {
			/* We're dealing with an OpenID 2.0 server, so let's set an ns
			Even though we should know location of the endpoint,
			we still need to verify it by discovery, so $server is not set here*/
			$params['openid.ns'] = 'http://specs.openid.net/auth/2.0';
		} elseif (isset($this->data['openid_claimed_id']) && $this->data['openid_claimed_id'] != $this->data['openid_identity']) {
			// If it's an OpenID 1 provider, and we've got claimed_id,
			// we have to append it to the returnUrl, like authUrl_v1 does.
			$this->returnUrl .= (strpos($this->returnUrl, '?') ? '&' : '?') . 'openid.claimed_id=' . $this->_claimedId;
		}

		if ($this->data['openid_return_to'] != $this->returnUrl) {
			// The return_to url must match the url of current request.
			// I'm assuing that noone will set the returnUrl to something that doesn't make sense.
			return false;
		}

		$serverInfo = $this->discover($this->_claimedId);

		foreach (explode(',', $this->data['openid_signed']) as $item) {
			/* Checking whether magic_quotes_gpc is turned on, because
			the function may fail if it is. For example, when fetching
			AX namePerson, it might containg an apostrophe, which will be escaped.
			In such case, validation would fail, since we'd send different data than OP
			wants to verify. stripslashes() should solve that problem, but we can't
			use it when magic_quotes is off.*/
			$value = $this->data['openid_' . str_replace('.', '_', $item)];
			$params['openid.' . $item] = get_magic_quotes_gpc() ? stripslashes($value) : $value;
		}

		$params['openid.mode'] = 'check_authentication';

		$response = $this->sendRequest($serverInfo['url'], 'POST', $params);

		return preg_match('/is_valid\s*:\s*true/i', $response);
	}

	/**
	 * Gets AX attributes provided by OP.
	 * @return array array of attributes.
	 */
	protected function fetchAxAttributes()
	{
		$alias = null;
		if (isset($this->data['openid_ns_ax']) && $this->data['openid_ns_ax'] != 'http://openid.net/srv/ax/1.0') {
			// It's the most likely case, so we'll check it before
			$alias = 'ax';
		} else {
			// 'ax' prefix is either undefined, or points to another extension, so we search for another prefix
			foreach ($this->data as $key => $value) {
				if (substr($key, 0, strlen('openid_ns_')) == 'openid_ns_' && $value == 'http://openid.net/srv/ax/1.0') {
					$alias = substr($key, strlen('openid_ns_'));
					break;
				}
			}
		}
		if (!$alias) {
			// An alias for AX schema has not been found, so there is no AX data in the OP's response
			return [];
		}

		$attributes = [];
		foreach ($this->data as $key => $value) {
			$keyMatch = 'openid_' . $alias . '_value_';
			if (substr($key, 0, strlen($keyMatch)) != $keyMatch) {
				continue;
			}
			$key = substr($key, strlen($keyMatch));
			if (!isset($this->data['openid_' . $alias . '_type_' . $key])) {
				/* OP is breaking the spec by returning a field without
				associated ns. This shouldn't happen, but it's better
				to check, than cause an E_NOTICE.*/
				continue;
			}
			$key = substr($this->data['openid_' . $alias . '_type_' . $key], strlen('http://axschema.org/'));
			$attributes[$key] = $value;
		}
		return $attributes;
	}

	/**
	 * Gets SREG attributes provided by OP. SREG names will be mapped to AX names.
	 * @return array array of attributes with keys being the AX schema names, e.g. 'contact/email'
	 */
	protected function fetchSregAttributes()
	{
		$attributes = [];
		$sregToAx = array_flip($this->axToSregMap);
		foreach ($this->data as $key => $value) {
			$keyMatch = 'openid_sreg_';
			if (substr($key, 0, strlen($keyMatch)) != $keyMatch) {
				continue;
			}
			$key = substr($key, strlen($keyMatch));
			if (!isset($sregToAx[$key])) {
				// The field name isn't part of the SREG spec, so we ignore it.
				continue;
			}
			$attributes[$sregToAx[$key]] = $value;
		}
		return $attributes;
	}

	/**
	 * Gets AX/SREG attributes provided by OP. Should be used only after successful validation.
	 * Note that it does not guarantee that any of the required/optional parameters will be present,
	 * or that there will be no other attributes besides those specified.
	 * In other words. OP may provide whatever information it wants to.
	 * SREG names will be mapped to AX names.
	 * @return array array of attributes with keys being the AX schema names, e.g. 'contact/email'
	 * @see http://www.axschema.org/types/
	 */
	public function fetchAttributes()
	{
		if (isset($this->data['openid_ns']) && $this->data['openid_ns'] == 'http://specs.openid.net/auth/2.0') {
			// OpenID 2.0
			// We search for both AX and SREG attributes, with AX taking precedence.
			return array_merge($this->fetchSregAttributes(), $this->fetchAxAttributes());
		}
		return $this->fetchSregAttributes();
	}
}