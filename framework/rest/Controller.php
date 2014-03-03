<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\VerbFilter;

/**
 * Controller is the base class for RESTful API controller classes.
 *
 * Controller implements the following steps in a RESTful API request handling cycle:
 *
 * 1. Resolving response format and API version number (see [[supportedFormats]], [[supportedVersions]] and [[version]]);
 * 2. Validating request method (see [[verbs()]]).
 * 3. Authenticating user (see [[authenticate()]]);
 * 4. Formatting response data (see [[serializeData()]]).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\web\Controller
{
	/**
	 * The name of the header parameter representing the API version number.
	 */
	const HEADER_VERSION = 'version';
	/**
	 * HTTP Basic authentication.
	 */
	const AUTH_TYPE_BASIC = 'Basic';
	/**
	 * HTTP Bearer authentication (the token obtained through OAuth2)
	 */
	const AUTH_TYPE_BEARER = 'Bearer';

	/**
	 * @var string|array the configuration for creating the serializer that formats the response data.
	 */
	public $serializer = 'yii\rest\Serializer';
	/**
	 * @inheritdoc
	 */
	public $enableCsrfValidation = false;
	/**
	 * @var string the authentication type. This should be a valid HTTP authentication method.
	 */
	public $authType = self::AUTH_TYPE_BASIC;
	/**
	 * @var string the authentication realm to display in case when authentication fails.
	 */
	public $authRealm = 'api';
	/**
	 * @var string the chosen API version number
	 * @see supportedVersions
	 */
	public $version;
	/**
	 * @var array list of supported API version numbers. If the current request does not specify a version
	 * number, the first element will be used as the chosen version number. For this reason, you should
	 * put the latest version number at the first.
	 */
	public $supportedVersions = ['1.0'];
	/**
	 * @var array list of supported response formats. The array keys are the requested content MIME types,
	 * and the array values are the corresponding response formats. The first element will be used
	 * as the response format if the current request does not specify a content type.
	 */
	public $supportedFormats = [
		'application/json' => Response::FORMAT_JSON,
		'application/xml' => Response::FORMAT_XML,
	];

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'verbFilter' => [
				'class' => VerbFilter::className(),
				'actions' => $this->verbs(),
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->resolveFormatAndVersion();
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {
			$this->authenticate();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function afterAction($action, $result)
	{
		$result = parent::afterAction($action, $result);
		return $this->serializeData($result);
	}

	/**
	 * Resolves the response format and the API version number.
	 * @throws BadRequestHttpException
	 */
	protected function resolveFormatAndVersion()
	{
		$this->version = reset($this->supportedVersions);
		Yii::$app->getResponse()->format = reset($this->supportedFormats);
		$types = Yii::$app->getRequest()->getAcceptableContentTypes();
		if (empty($types)) {
			$types['*/*'] = [];
		}

		foreach ($types as $type => $params) {
			if (isset($this->supportedFormats[$type])) {
				Yii::$app->getResponse()->format = $this->supportedFormats[$type];
				if (isset($params[self::HEADER_VERSION])) {
					if (in_array($params[self::HEADER_VERSION], $this->supportedVersions, true)) {
						$this->version = $params[self::HEADER_VERSION];
					} else {
						throw new BadRequestHttpException('You are requesting an invalid version number.');
					}
				}
				return;
			}
		}

		if (!isset($types['*/*'])) {
			throw new BadRequestHttpException('None of your requested content types is valid.');
		}
	}

	/**
	 * Declares the allowed HTTP verbs.
	 * Please refer to [[VerbFilter::actions]] on how to declare the allowed verbs.
	 * @return array the allowed HTTP verbs.
	 */
	protected function verbs()
	{
		return [];
	}

	/**
	 * Authenticates the user.
	 * This method implements the user authentication based on an access token sent through the `Authorization` HTTP header.
	 * @throws UnauthorizedHttpException if the user is not authenticated successfully
	 */
	protected function authenticate()
	{
		$request = Yii::$app->getRequest();
		if ($this->authType == self::AUTH_TYPE_BASIC) {
			$accessToken = $request->getAuthUser();
		} else {
			$authHeader = $request->getHeaders()->get('Authorization');
			if ($authHeader !== null && preg_match("/^{$this->authType}\\s+(.*?)$/", $authHeader, $matches)) {
				$accessToken = $matches[1];
			}
		}

		if (empty($accessToken) || !Yii::$app->getUser()->loginByToken($accessToken)) {
			Yii::$app->getResponse()->getHeaders()->set("WWW-Authenticate', '{$this->authType} realm=\"{$this->authRealm}\"");
			throw new UnauthorizedHttpException(empty($accessToken) ? 'Access token required.' : 'You are requesting with an invalid access token.');
		}
	}

	/**
	 * Serializes the specified data.
	 * The default implementation will create a serializer based on the configuration given by [[serializer]].
	 * It then uses the serializer to serialize the given data.
	 * @param mixed $data the data to be serialized
	 * @return mixed the serialized data.
	 */
	protected function serializeData($data)
	{
		return Yii::createObject($this->serializer)->serialize($data);
	}
}
