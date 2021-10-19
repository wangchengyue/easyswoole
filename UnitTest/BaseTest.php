<?php

namespace UnitTest;

use Curl\Curl;
use EasySwoole\EasySwoole\Core;
use PHPUnit\Framework\TestCase;

/**
 * BaseTest
 * Class BaseTest
 * Create With ClassGeneration
 */
class BaseTest extends TestCase
{
	public static $isInit = 0;

	/** @var Curl */
	public $curl;
	public $apiBase = 'http://127.0.0.1:9501';
	public $modelName;


	public function request($action, $data = [], $modelName = null)
	{
		$modelName = $modelName ?? $this->modelName;
		$url = $this->apiBase . '/' . $modelName . '/' . $action;
		$curl = $this->curl;
		$curl->post($url, $data);
		if ($curl->response) {
		//            var_dump($curl->response);
		} else {
		    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "
		";
		}
		$this->assertTrue(!!$curl->response);
		$this->assertEquals(200, $curl->response->code, $curl->response->msg);
		return $curl->response;
	}


	protected function setUp(): void
	{
		$this->curl = new Curl();
		if (self::$isInit == 1) {
		    return;
		}
		require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
		defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', dirname(__FILE__, 2));
		require_once dirname(__FILE__, 2) . '/EasySwooleEvent.php';
		Core::getInstance()->initialize();
		self::$isInit = 1;
	}
}

