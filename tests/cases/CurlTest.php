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
		$this->assertSame("some_url", curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
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
		$this->assertSame(CURLE_URL_MALFORMAT, curl_errno($curl));
//		$this->assertSame("No URL set!", curl_error($curl));
		curl_close($curl);
	}

	/**
	 * @covers curl_exec
	 * @covers Curl::exec
	 */
	public function testGetExecHttp() {
		$server = new Testo\Server(EchoResponse::class);
		$expected = '{"method":"GET","get":{"asdf":"3423"},"post":[]}';

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
		$this->assertSame(200, curl_getinfo($curl, CURLINFO_HTTP_CODE));
		curl_close($curl);
	}
}
