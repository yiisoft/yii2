<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\openid;

use yii\base\Component;
use yii\base\Exception;
use yii\base\NotSupportedException;

/**
 * Class Client
 *
 * @see http://openid.net/
 *
 * @property string $returnUrl ???
 * @property mixed $identity ???
 * @property string $trustRoot ???
 * @property string $realm alias of [[trustRoot]].
 * @property mixed $mode ??? This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Client extends Component
{
	public $required = [];
	public $optional = [];
	public $verify_peer;
	public $capath;
	public $cainfo;

	private $_returnUrl;
	private $_identity;
	private $claimed_id;
	private $_trustRoot;

	protected $server;
	protected $version;

	protected $aliases;
	protected $identifier_select = false;
	protected $ax = false;
	protected $sreg = false;
	protected $data;

	public static $axToSregMap = [
		'namePerson/friendly'     => 'nickname',
		'contact/email'           => 'email',
		'namePerson'              => 'fullname',
		'birthDate'               => 'dob',
		'person/gender'           => 'gender',
		'contact/postalCode/home' => 'postcode',
		'contact/country/home'    => 'country',
		'pref/language'           => 'language',
		'pref/timezone'           => 'timezone',
	];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->data = $_POST + $_GET; # OPs may send data as POST or GET.
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
		$this->claimed_id = $value;
	}

	public function setReturnUrl($returnUrl)
	{
		$this->_returnUrl = $returnUrl;
	}

	public function getReturnUrl()
	{
		if ($this->_returnUrl === null) {
			$uri = rtrim(preg_replace('#((?<=\?)|&)openid\.[^&]+#', '', $_SERVER['REQUEST_URI']), '?');
			$this->_returnUrl = $this->getTrustRoot() . $uri;
		}
		return $this->_returnUrl;
	}

	public function getIdentity()
	{
		# We return claimed_id instead of identity,
		# because the developer should see the claimed identifier,
		# i.e. what he set as identity, not the op-local identifier (which is what we verify)
		return $this->claimed_id;
	}

	public function setTrustRoot($value)
	{
		$this->_trustRoot = trim($value);
	}

	public function getTrustRoot()
	{
		if ($this->_trustRoot === null) {
			$this->_trustRoot = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		}
		return $this->_trustRoot;
	}

	public function setRealm($value)
	{
		$this->setTrustRoot($value);
	}

	public function getRealm()
	{
		return $this->getTrustRoot();
	}

	public function getMode()
	{
		return empty($this->data['openid_mode']) ? null : $this->data['openid_mode'];
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

	protected function sendCurlRequest($url, $method = 'GET', $params = [])
	{
		$params = http_build_query($params, '', '&');
		$curl = curl_init($url . ($method == 'GET' && $params ? '?' . $params : ''));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/xrds+xml, */*'));

		if ($this->verify_peer !== null) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verify_peer);
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
				$pos = strpos($header,':');
				$name = strtolower(trim(substr($header, 0, $pos)));
				$headers[$name] = trim(substr($header, $pos+1));
			}

			# Updating claimed_id in case of redirections.
			$effective_url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
			if ($effective_url != $url) {
				$this->identity = $this->claimed_id = $effective_url;
			}

			return $headers;
		}

		if (curl_errno($curl)) {
			throw new Exception(curl_error($curl), curl_errno($curl));
		}

		return $response;
	}

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
				# We want to send a HEAD request,
				# but since get_headers doesn't accept $context parameter,
				# we have to change the defaults.
				$default = stream_context_get_options(stream_context_get_default());
				stream_context_get_default([
					'http' => [
						'method' => 'HEAD',
						'header' => 'Accept: application/xrds+xml, */*',
						'ignore_errors' => true,
					]
				]);

				$url = $url . ($params ? '?' . $params : '');
				$headers_tmp = get_headers($url);
				if (!$headers_tmp) {
					return [];
				}

				# Parsing headers.
				$headers = [];
				foreach ($headers_tmp as $header) {
					$pos = strpos($header, ':');
					$name = strtolower(trim(substr($header, 0, $pos)));
					$headers[$name] = trim(substr($header, $pos+1));

					# Following possible redirections. The point is just to have
					# claimed_id change with them, because get_headers() will
					# follow redirections automatically.
					# We ignore redirections with relative paths.
					# If any known provider uses them, file a bug report.
					if ($name == 'location') {
						if (strpos($headers[$name], 'http') === 0) {
							$this->identity = $this->claimed_id = $headers[$name];
						} elseif($headers[$name][0] == '/') {
							$parsed_url = parse_url($this->claimed_id);
							$this->identity =
							$this->claimed_id = $parsed_url['scheme'] . '://'
								. $parsed_url['host']
								. $headers[$name];
						}
					}
				}

				# And restore them.
				stream_context_get_default($default);
				return $headers;
			default:
				throw new NotSupportedException("Method {$method} not supported");
		}

		if ($this->verify_peer) {
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
	 * Performs Yadis and HTML discovery. Normally not used.
	 * @param string $url Identity URL.
	 * @return string OP Endpoint (i.e. OpenID provider address).
	 * @throws Exception
	 */
	public function discover($url)
	{
		if (!$url) {
			throw new Exception('No identity supplied.');
		}
		# Use xri.net proxy to resolve i-name identities
		if (!preg_match('#^https?:#', $url)) {
			$url = "https://xri.net/$url";
		}

		# We save the original url in case of Yadis discovery failure.
		# It can happen when we'll be lead to an XRDS document
		# which does not have any OpenID2 services.
		$originalUrl = $url;

		# A flag to disable yadis discovery in case of failure in headers.
		$yadis = true;

		# We'll jump a maximum of 5 times, to avoid endless redirections.
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
					# Apparently, some providers return XRDS documents as text/html.
					# While it is against the spec, allowing this here shouldn't break
					# compatibility with anything.
					# ---
					# Found an XRDS document, now let's find the server, and optionally delegate.
					$content = $this->sendRequest($url, 'GET');

					preg_match_all('#<Service.*?>(.*?)</Service>#s', $content, $m);
					foreach ($m[1] as $content) {
						$content = ' ' . $content; # The space is added, so that strpos doesn't return 0.

						# OpenID 2
						$ns = preg_quote('http://specs.openid.net/auth/2.0/');
						if (preg_match('#<Type>\s*'.$ns.'(server|signon)\s*</Type>#s', $content, $type)) {
							if ($type[1] == 'server') {
								$this->identifier_select = true;
							}

							preg_match('#<URI.*?>(.*)</URI>#', $content, $server);
							preg_match('#<(Local|Canonical)ID>(.*)</\1ID>#', $content, $delegate);
							if (empty($server)) {
								return false;
							}
							# Does the server advertise support for either AX or SREG?
							$this->ax   = (bool) strpos($content, '<Type>http://openid.net/srv/ax/1.0</Type>');
							$this->sreg = strpos($content, '<Type>http://openid.net/sreg/1.0</Type>')
								|| strpos($content, '<Type>http://openid.net/extensions/sreg/1.1</Type>');

							$server = $server[1];
							if (isset($delegate[2])) {
								$this->identity = trim($delegate[2]);
							}
							$this->version = 2;

							$this->server = $server;
							return $server;
						}

						# OpenID 1.1
						$ns = preg_quote('http://openid.net/signon/1.1');
						if (preg_match('#<Type>\s*'.$ns.'\s*</Type>#s', $content)) {

							preg_match('#<URI.*?>(.*)</URI>#', $content, $server);
							preg_match('#<.*?Delegate>(.*)</.*?Delegate>#', $content, $delegate);
							if (empty($server)) {
								return false;
							}
							# AX can be used only with OpenID 2.0, so checking only SREG
							$this->sreg = strpos($content, '<Type>http://openid.net/sreg/1.0</Type>')
								|| strpos($content, '<Type>http://openid.net/extensions/sreg/1.1</Type>');

							$server = $server[1];
							if (isset($delegate[1])) {
								$this->identity = $delegate[1];
							}
							$this->version = 1;

							$this->server = $server;
							return $server;
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

				# There are no relevant information in headers, so we search the body.
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

			# At this point, the YADIS Discovery has failed, so we'll switch
			# to openid2 HTML discovery, then fallback to openid 1.1 discovery.
			$server = $this->extractHtmlTagValue($content, 'link', 'rel', 'openid2.provider', 'href');
			$delegate = $this->extractHtmlTagValue($content, 'link', 'rel', 'openid2.local_id', 'href');
			$this->version = 2;

			if (!$server) {
				# The same with openid 1.1
				$server   = $this->extractHtmlTagValue($content, 'link', 'rel', 'openid.server', 'href');
				$delegate = $this->extractHtmlTagValue($content, 'link', 'rel', 'openid.delegate', 'href');
				$this->version = 1;
			}

			if ($server) {
				# We found an OpenID2 OP Endpoint
				if ($delegate) {
					# We have also found an OP-Local ID.
					$this->identity = $delegate;
				}
				$this->server = $server;
				return $server;
			}
			throw new Exception('No servers found!');
		}
		throw new Exception('Endless redirection!');
	}

	protected function sregParams()
	{
		$params = [];
		# We always use SREG 1.1, even if the server is advertising only support for 1.0.
		# That's because it's fully backwards compatibile with 1.0, and some providers
		# advertise 1.0 even if they accept only 1.1. One such provider is myopenid.com
		$params['openid.ns.sreg'] = 'http://openid.net/extensions/sreg/1.1';
		if ($this->required) {
			$params['openid.sreg.required'] = [];
			foreach ($this->required as $required) {
				if (!isset(self::$axToSregMap[$required])) {
					continue;
				}
				$params['openid.sreg.required'][] = self::$axToSregMap[$required];
			}
			$params['openid.sreg.required'] = implode(',', $params['openid.sreg.required']);
		}

		if ($this->optional) {
			$params['openid.sreg.optional'] = [];
			foreach ($this->optional as $optional) {
				if (!isset(self::$axToSregMap[$optional])) {
					continue;
				}
				$params['openid.sreg.optional'][] = self::$axToSregMap[$optional];
			}
			$params['openid.sreg.optional'] = implode(',', $params['openid.sreg.optional']);
		}
		return $params;
	}

	protected function axParams()
	{
		$params = [];
		if ($this->required || $this->optional) {
			$params['openid.ns.ax'] = 'http://openid.net/srv/ax/1.0';
			$params['openid.ax.mode'] = 'fetch_request';
			$this->aliases = [];
			$counts = [];
			$required = [];
			$optional = [];
			foreach (['required', 'optional'] as $type) {
				foreach ($this->$type as $alias => $field) {
					if (is_int($alias)) {
						$alias = strtr($field, '/', '_');
					}
					$this->aliases[$alias] = 'http://axschema.org/' . $field;
					if (empty($counts[$alias])) {
						$counts[$alias] = 0;
					}
					$counts[$alias] += 1;
					${$type}[] = $alias;
				}
			}
			foreach ($this->aliases as $alias => $ns) {
				$params['openid.ax.type.' . $alias] = $ns;
			}
			foreach ($counts as $alias => $count) {
				if ($count == 1) {
					continue;
				}
				$params['openid.ax.count.' . $alias] = $count;
			}

			# Don't send empty ax.requied and ax.if_available.
			# Google and possibly other providers refuse to support ax when one of these is empty.
			if ($required) {
				$params['openid.ax.required'] = implode(',', $required);
			}
			if ($optional) {
				$params['openid.ax.if_available'] = implode(',', $optional);
			}
		}
		return $params;
	}

	protected function authUrlV1()
	{
		$returnUrl = $this->returnUrl;
		# If we have an openid.delegate that is different from our claimed id,
		# we need to somehow preserve the claimed id between requests.
		# The simplest way is to just send it along with the return_to url.
		if ($this->identity != $this->claimed_id) {
			$returnUrl .= (strpos($returnUrl, '?') ? '&' : '?') . 'openid.claimed_id=' . $this->claimed_id;
		}

		$params = array_merge(
			$this->sregParams(),
			[
				'openid.return_to' => $returnUrl,
				'openid.mode' => 'checkid_setup',
				'openid.identity' => $this->identity,
				'openid.trust_root' => $this->trustRoot,
			]
		);

		return $this->buildUrl(parse_url($this->server), ['query' => http_build_query($params, '', '&')]);
	}

	protected function authUrlV2($identifierSelect)
	{
		$params = [
			'openid.ns' => 'http://specs.openid.net/auth/2.0',
			'openid.mode' => 'checkid_setup',
			'openid.return_to' => $this->returnUrl,
			'openid.realm' => $this->trustRoot,
		];
		if ($this->ax) {
			$params = array_merge($this->axParams(), $params);
		}
		if ($this->sreg) {
			$params = array_merge($this->sregParams(), $params);
		}
		if (!$this->ax && !$this->sreg) {
			# If OP doesn't advertise either SREG, nor AX, let's send them both
			# in worst case we don't get anything in return.
			$params = array_merge($this->sregParams(), $this->axParams(), $params);
		}

		if ($identifierSelect) {
			$url = 'http://specs.openid.net/auth/2.0/identifier_select';
			$params['openid.identity'] = $url;
			$params['openid.claimed_id']= $url;
		} else {
			$params['openid.identity'] = $this->identity;
			$params['openid.claimed_id'] = $this->claimed_id;
		}

		return $this->buildUrl(parse_url($this->server), ['query' => http_build_query($params, '', '&')]);
	}

	/**
	 * Returns authentication URL. Usually, you want to redirect your user to it.
	 * @param string $identifier_select Whether to request OP to select identity for an user in OpenID 2. Does not affect OpenID 1.
	 * @return string the authentication URL.
	 * @throws Exception
	 */
	public function authUrl($identifier_select = null)
	{
		if (!$this->server) {
			$this->discover($this->identity);
		}
		if ($this->version == 2) {
			if ($identifier_select === null) {
				return $this->authUrlV2($this->identifier_select);
			}
			return $this->authUrlV2($identifier_select);
		}
		return $this->authUrlV1();
	}

	/**
	 * Performs OpenID verification with the OP.
	 * @return boolean whether the verification was successful.
	 * @throws Exception
	 */
	public function validate()
	{
		$this->claimed_id = isset($this->data['openid_claimed_id']) ? $this->data['openid_claimed_id'] : $this->data['openid_identity'];
		$params = [
			'openid.assoc_handle' => $this->data['openid_assoc_handle'],
			'openid.signed' => $this->data['openid_signed'],
			'openid.sig' => $this->data['openid_sig'],
		];

		if (isset($this->data['openid_ns'])) {
			# We're dealing with an OpenID 2.0 server, so let's set an ns
			# Even though we should know location of the endpoint,
			# we still need to verify it by discovery, so $server is not set here
			$params['openid.ns'] = 'http://specs.openid.net/auth/2.0';
		} elseif (isset($this->data['openid_claimed_id'])
			&& $this->data['openid_claimed_id'] != $this->data['openid_identity']
		) {
			# If it's an OpenID 1 provider, and we've got claimed_id,
			# we have to append it to the returnUrl, like authUrl_v1 does.
			$this->returnUrl .= (strpos($this->returnUrl, '?') ? '&' : '?')
				. 'openid.claimed_id=' . $this->claimed_id;
		}

		if ($this->data['openid_return_to'] != $this->returnUrl) {
			# The return_to url must match the url of current request.
			# I'm assuing that noone will set the returnUrl to something that doesn't make sense.
			return false;
		}

		$server = $this->discover($this->claimed_id);

		foreach (explode(',', $this->data['openid_signed']) as $item) {
			# Checking whether magic_quotes_gpc is turned on, because
			# the function may fail if it is. For example, when fetching
			# AX namePerson, it might containg an apostrophe, which will be escaped.
			# In such case, validation would fail, since we'd send different data than OP
			# wants to verify. stripslashes() should solve that problem, but we can't
			# use it when magic_quotes is off.
			$value = $this->data['openid_' . str_replace('.', '_', $item)];
			$params['openid.' . $item] = get_magic_quotes_gpc() ? stripslashes($value) : $value;
		}

		$params['openid.mode'] = 'check_authentication';

		$response = $this->sendRequest($server, 'POST', $params);

		return preg_match('/is_valid\s*:\s*true/i', $response);
	}

	protected function getAxAttributes()
	{
		$alias = null;
		if (isset($this->data['openid_ns_ax']) && $this->data['openid_ns_ax'] != 'http://openid.net/srv/ax/1.0') {
			# It's the most likely case, so we'll check it before
			$alias = 'ax';
		} else {
			# 'ax' prefix is either undefined, or points to another extension,
			# so we search for another prefix
			foreach ($this->data as $key => $value) {
				if (substr($key, 0, strlen('openid_ns_')) == 'openid_ns_' && $value == 'http://openid.net/srv/ax/1.0') {
					$alias = substr($key, strlen('openid_ns_'));
					break;
				}
			}
		}
		if (!$alias) {
			# An alias for AX schema has not been found,
			# so there is no AX data in the OP's response
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
				# OP is breaking the spec by returning a field without
				# associated ns. This shouldn't happen, but it's better
				# to check, than cause an E_NOTICE.
				continue;
			}
			$key = substr($this->data['openid_' . $alias . '_type_' . $key], strlen('http://axschema.org/'));
			$attributes[$key] = $value;
		}
		return $attributes;
	}

	protected function getSregAttributes()
	{
		$attributes = array();
		$sregToAx = array_flip(self::$axToSregMap);
		foreach ($this->data as $key => $value) {
			$keyMatch = 'openid_sreg_';
			if (substr($key, 0, strlen($keyMatch)) != $keyMatch) {
				continue;
			}
			$key = substr($key, strlen($keyMatch));
			if (!isset($sregToAx[$key])) {
				# The field name isn't part of the SREG spec, so we ignore it.
				continue;
			}
			$attributes[$sregToAx[$key]] = $value;
		}
		return $attributes;
	}

	/**
	 * Gets AX/SREG attributes provided by OP. should be used only after successful validaton.
	 * Note that it does not guarantee that any of the required/optional parameters will be present,
	 * or that there will be no other attributes besides those specified.
	 * In other words. OP may provide whatever information it wants to.
	 * SREG names will be mapped to AX names.
	 * @return array array of attributes with keys being the AX schema names, e.g. 'contact/email'
	 * @see http://www.axschema.org/types/
	 */
	public function getAttributes()
	{
		if (isset($this->data['openid_ns']) && $this->data['openid_ns'] == 'http://specs.openid.net/auth/2.0') {
			# OpenID 2.0
			# We search for both AX and SREG attributes, with AX taking precedence.
			return array_merge($this->getSregAttributes(), $this->getAxAttributes());
		}
		return $this->getSregAttributes();
	}
}