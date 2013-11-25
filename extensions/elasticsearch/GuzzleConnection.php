<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\elasticsearch;


use Guzzle\Http\Exception\ClientErrorResponseException;
use yii\base\Exception;
use yii\helpers\Json;

class GuzzleConnection extends Connection
{
	/**
	 * @var \Guzzle\Http\Client
	 */
	private $_http;

	protected function httpRequest($type, $url, $body = null)
	{
		if ($this->_http === null) {
			$this->_http = new \Guzzle\Http\Client('http://localhost:9200/');// TODO use active node
			//$guzzle->setDefaultOption()
		}
		$requestOptions = [];
		if ($type == 'head') {
			$requestOptions['exceptions'] = false;
		}
		if ($type == 'get' && $body !== null) {
			$type = 'post';
		}
		try{
			$response = $this->_http->createRequest(
				strtoupper($type)
				, $url,
				null,
				$body,
				$requestOptions
			)->send();
		} catch(ClientErrorResponseException $e) {
			if ($e->getResponse()->getStatusCode() == 404) {
				return false;
			}
			throw new Exception("elasticsearch error:\n\n"
				. $body . "\n\n" . $e->getMessage()
				. print_r(Json::decode($e->getResponse()->getBody(true)), true), 0, $e);
		}
		if ($type == 'head') {
			return $response->getStatusCode() == 200;
		}
		return Json::decode($response->getBody(true));
	}

}