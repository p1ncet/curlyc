<?php

namespace Curlyc;

use Testo;

/**
 * Class CurlTest
 * @package Curlyc
 * @coversDefaultClass Curlyc/Curl
 * @backupStaticAttributes enabled
 */
class CurlTest extends \PHPUnit_Framework_TestCase{

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
		$server = new Testo\Server(Testo\SimpleResponse::class);
		$curl = curl_init($server->getUrl() ."/test?asdf=3423");
		ob_start();
		$this->assertTrue(curl_exec($curl));
		$content = ob_get_contents();
		ob_end_clean();
		$expected = '{"headers":{"Host":"127.0.0.1:12345","Accept":"*/*"},"host":"127.0.0.1:12345","uri":"/test?asdf=3423","get":{"asdf":"3423"},"post":[]}';
		$this->assertSame($expected, $content);
		curl_close($curl);
	}
}