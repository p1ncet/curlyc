<?php

namespace Curlyc;

use Curlyc\Blank\EchoResponse;
use Testo;

/**
 * Class CurlTest
 * @package Curlyc
 * @coversDefaultClass Curlyc/Curl
 * @backupStaticAttributes enabled
 */
class CurlTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers curl_init
	 * @covers Curl::__construct
	 */
	public function testCurlInit() {
		$curl = curl_init();
		$this->assertTrue(is_resource($curl) || is_object($curl));
		$info = curl_getinfo($curl);
		$this->assertInternalType("array", $info);
		$expected = [
			//<editor-fold desc="..expected..">
			"url" => "",
			"content_type" => null,
			"http_code" => 0,
			"header_size" => 0,
			"request_size" => 0,
			"filetime" => 0,
			"ssl_verify_result" => 0,
			"redirect_count" => 0,
			"total_time" => 0.0,
			"namelookup_time" => 0.0,
			"connect_time" => 0.0,
			"pretransfer_time" => 0.0,
			"size_upload" => 0.0,
			"size_download" => 0.0,
			"speed_download" => 0.0,
			"speed_upload" => 0.0,
			"download_content_length" => -1.0,
			"upload_content_length" => -1.0,
			"starttransfer_time" => 0.0,
			"redirect_time" => 0.0,
			"redirect_url" => "",
			"primary_ip" => "",
			"certinfo" => [],
			"primary_port" => 0,
			"local_ip" => "",
			"local_port" => 0,
			//</editor-fold>
		];
		$this->assertSame($expected, $info);
		$this->assertSame(false, curl_getinfo($curl, CURLOPT_URL));
		curl_close($curl);
	}

	/**
	 * @covers curl_init
	 * @covers Curl::__construct
	 */
	public function testCurlInitWithUrl() {
		$curl = curl_init("some_url");
		$this->assertTrue(is_resource($curl) || is_object($curl));
		$info = curl_getinfo($curl);
		$this->assertInternalType("array", $info);
		$expected = [
			//<editor-fold desc="..expected..">
			"url" => "some_url",
			"content_type" => null,
			"http_code" => 0,
			"header_size" => 0,
			"request_size" => 0,
			"filetime" => 0,
			"ssl_verify_result" => 0,
			"redirect_count" => 0,
			"total_time" => 0.0,
			"namelookup_time" => 0.0,
			"connect_time" => 0.0,
			"pretransfer_time" => 0.0,
			"size_upload" => 0.0,
			"size_download" => 0.0,
			"speed_download" => 0.0,
			"speed_upload" => 0.0,
			"download_content_length" => -1.0,
			"upload_content_length" => -1.0,
			"starttransfer_time" => 0.0,
			"redirect_time" => 0.0,
			"redirect_url" => "",
			"primary_ip" => "",
			"certinfo" => [],
			"primary_port" => 0,
			"local_ip" => "",
			"local_port" => 0,
			//</editor-fold>
		];
		$this->assertSame($expected, $info);
		curl_close($curl);
	}

	/**
	 * @covers curl_close
	 * @covers Curl::close
	 */
	public function testCurlClose() {
		$curl = curl_init();
		$this->assertTrue(is_resource($curl) || is_object($curl));
		curl_close($curl);
		$this->assertFalse(is_resource($curl) || is_object($curl));
	}

	/**
	 * @covers curl_exec
	 * @covers Curl::exec
	 */
	public function testGetExecEmpty() {
		$curl = curl_init();
		$this->assertFalse(curl_exec($curl));
		curl_close($curl);
	}

	/**
	 * @covers curl_exec
	 * @covers Curl::exec
	 */
	public function testGetExecHttp() {
		$server = new Testo\Server(EchoResponse::class);
		$expected = '{"get":{"asdf":"3423"},"post":[]}';

		// dump result case
		$curl = curl_init($server->getUrl() ."/test?asdf=3423");
		ob_start();
		$this->assertTrue(curl_exec($curl));
		$content = ob_get_contents();
		ob_end_clean();
		$this->assertSame($expected, $content);
		curl_close($curl);

		// return result case
		$curl = curl_init($server->getUrl() ."/test?asdf=3423");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($curl);
		$this->assertSame($expected, $content);
		curl_close($curl);
	}

	/**
	 * With CURLOPT_HEADER option we'll get response with plain headers
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptHeader() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl() ."/test?asdf=3423");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		$content = curl_exec($curl);
		curl_close($curl);

		$headers = [
			"HTTP/1.1 200 OK",
			"Host: 127.0.0.1:12345",
			"Connection: close",
			"X-Powered-By: PHP/5.5.31",
			"Content-type: text/html",
		];
		$expected = '{"get":{"asdf":"3423"},"post":[]}';
		$this->assertSame(implode("\r\n", $headers) . "\r\n\r\n" . $expected, $content);
	}

	/**
	 * With CURLOPT_USERAGENT option we'll send specified user-agent
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptUserAgent() {
		$test_user_agent = "test-user-agent";
		$server = new Testo\Server(Testo\SimpleResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_USERAGENT, $test_user_agent);
		$content = curl_exec($curl);
		curl_close($curl);

		$content = json_decode($content, 1);
		$this->assertSame($test_user_agent, $content["headers"]["User-Agent"]);
	}
}
