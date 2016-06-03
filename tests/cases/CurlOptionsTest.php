<?php

namespace Curlyc;

use Curlyc\Blank\EchoResponse;
use Curlyc\Blank\GzipResponse;
use Curlyc\Blank\SleepResponse;
use Testo;

/**
 * Class CurlTest
 * @package Curlyc
 * @coversDefaultClass Curlyc/Curl
 * @backupStaticAttributes enabled
 */
class CurlOptionsTest extends \PHPUnit_Framework_TestCase {

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
		$expected = '{"method":"GET","get":{"asdf":"3423"},"post":[]}';
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

	/**
	 * With CURLOPT_HTTPHEADER option we'll send specified headers
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptHttpHeader() {
		$expected = "Bearer bla-bla-bla";
		$expected2 = "test";
		$headers = ["Authorization: $expected", "Authorization2: $expected2"];
		$server = new Testo\Server(Testo\SimpleResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$content = curl_exec($curl);
		curl_close($curl);

		$content = json_decode($content, 1);
		$this->assertArrayHasKey("Authorization", $content["headers"]);
		$this->assertSame($expected, $content["headers"]["Authorization"]);
		$this->assertArrayHasKey("Authorization2", $content["headers"]);
		$this->assertSame($expected2, $content["headers"]["Authorization2"]);
	}

	/**
	 * With CURLOPT_POST option we'll send post with fields specified by CURLOPT_POSTFIELDS option
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptPost() {
		$server = new Testo\Server(EchoResponse::class);

		// without POST fields case
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		$content = curl_exec($curl);
		$this->assertSame('{"method":"POST","get":[],"post":[]}', $content);
		curl_close($curl);


		// with POST fields case
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $fs = ["field1" => "value2", "field3" => "value4"]);
		$content = curl_exec($curl);
		$this->assertSame('{"method":"POST","get":[],"post":{"field1":"value2","field3":"value4"}}', $content);
		curl_close($curl);

		// GET method with POST fields case - it will be POST
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 0);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $fs);
		$content = curl_exec($curl);
		$this->assertSame('{"method":"POST","get":[],"post":{"field1":"value2","field3":"value4"}}', $content);
		curl_close($curl);
	}

	/**
	 * Option CURLOPT_CONNECTTIMEOUT set timeout for connection
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptConnectTimeout() {
		$curl = curl_init("10.255.255.1"); // connect to a non-routable ip address
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
		$time = microtime(true);
		$content = curl_exec($curl);
		var_dump($content);
		$this->assertGreaterThan(2, microtime(true) - $time);
		$this->assertFalse($content);
		$this->assertSame(CURLE_OPERATION_TIMEOUTED, curl_errno($curl));
		$this->assertContains("Connection timed out after ", curl_error($curl));
		curl_close($curl);
	}

	/**
	 * Option CURLOPT_TIMEOUT set timeout for execution
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptTimeout() {
		$server = new Testo\Server(SleepResponse::class);
		$curl = curl_init($server->getUrl() . "/?sleep=5");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 2);
		$time = microtime(true);
		$content = curl_exec($curl);
		$time = microtime(true) - $time;
		$this->assertGreaterThan(2, $time);
		$this->assertLessThan(3, $time);
		$this->assertFalse($content);
		$this->assertSame(CURLE_OPERATION_TIMEOUTED, curl_errno($curl));
		$this->assertContains("Operation timed out after ", curl_error($curl));
		curl_close($curl);
	}

	/**
	 * @todo
	 * Options CURLOPT_SSL_VERIFYPEER and CURLOPT_SSL_VERIFYHOST
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptSslVerifyPeer() {
//		$server = new Testo\Server(EchoResponse::class);
//		$curl = curl_init($server->getUrl());
//		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//		$server->sleep();
//		$time = microtime(true);
//		$content = curl_exec($curl);
//		$time = microtime(true) - $time;
//		$this->assertGreaterThan(2, $time);
//		$this->assertLessThan(3, $time);
//		$this->assertFalse($content);
//		curl_close($curl);
	}

	/**
	 * @todo
	 * Option CURLOPT_CAINFO set file with certificates
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptCaInfo() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CAINFO, __DIR__ . "/../fb_ca_chain_bundle.crt");
	}

	/**
	 * @todo
	 * Option CURLOPT_CAPATH set dir with files with certificates
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptCaPath() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CAPATH, __DIR__ . "/../fb_ca_chain_bundle.crt");
	}
	
	/**
	 * Option CURLOPT_ENCODING set header Accept-Encoding for decode gzip,deflate
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptEncoding() {
		$server = new Testo\Server(GzipResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_ENCODING, "gzip,deflate");
		$content = curl_exec($curl);
		$this->assertSame('{"method":"GET","get":[],"post":[]}', $content);
		curl_close($curl);

		// without CURLOPT_ENCODING option - get uncompressed response
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($curl);
		$this->assertSame('{"method":"GET","get":[],"post":[]}', gzuncompress($content));
		curl_close($curl);

		// with empty CURLOPT_ENCODING option will accept any encoding
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_ENCODING, "");
		$content = curl_exec($curl);
		$this->assertSame('{"method":"GET","get":[],"post":[]}', $content);
		curl_close($curl);

		// check headers
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_ENCODING, "");
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_NOBODY, 1);
		curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
		$headers = [];
		foreach (explode("\r\n", curl_exec($curl)) as $header) {
			$header = explode(": ", $header);
			if (count($header) == 2) {
				$headers[$header[0]] = $header[1];
			}
		}
		$this->assertArrayHasKey("Content-Encoding", $headers);
		$this->assertSame("gzip", $headers["Content-Encoding"]);
		$this->assertContains("Accept-Encoding: deflate, gzip", curl_getinfo($curl, CURLINFO_HEADER_OUT));
		curl_close($curl);
	}

	/**
	 * @todo
	 * Option CURLOPT_PROXY set proxy, option CURLOPT_PROXYPORT to set proxy port, CURLOPT_PROXYUSERPWD
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptProxy() {
//		$server = new Testo\Server(Testo\SimpleResponse::class);
//		$proxy = new Testo\Server(Testo\SimpleResponse::class, '127.0.0.1', 12321);
//		$curl = curl_init($server->getUrl());
//		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//		curl_setopt($curl, CURLOPT_PROXY, $proxy->getUrl());
//		$content = curl_exec($curl);
//		var_dump($content);
//		$this->assertSame('{"method":"GET","get":[],"post":[]}', $content);
//		curl_close($curl);
	}

	/**
	 * @covers curl_setopt_array
	 * @covers Curl::setOptArray
	 */
	public function testCurlSetOptArray() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl());
		$options = [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => $server->getUrl(),
			CURLOPT_POSTFIELDS     => http_build_query(["field1" => "value2"], null, '&'),
		];
		curl_setopt_array($curl, $options);
		$content = curl_exec($curl);
		$this->assertSame('{"method":"POST","get":[],"post":{"field1":"value2"}}', $content);
		curl_close($curl);
	}

	/**
	 * Option CURLOPT_USERPWD set username:password for basic authorization
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptUserPwd() {
		$server = new Testo\Server(Testo\SimpleResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERPWD, "bobbi_name:bobbi_pwd");
		$content = curl_exec($curl);
		$content = json_decode($content, 1);
		$this->assertArrayHasKey("Authorization", $content["headers"]);
		$this->assertSame("Basic Ym9iYmlfbmFtZTpib2JiaV9wd2Q=", $content["headers"]["Authorization"]);
		curl_close($curl);
	}

	/**
	 * @todo
	 * Option CURLOPT_STDERR set filename to put errors instead STDERR
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptStrErr() {
	}

	/**
	 * @todo
	 * Option CURLOPT_VERBOSE write addition info to STDERR
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptVerbose() {
	}

	/**
	 * Option CURLOPT_HTTP_VERSION set http version
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptHttpVersion() {
		$server = new Testo\Server(EchoResponse::class);
		// http version 1.1 case
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_NOBODY, 1);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		$headers = curl_exec($curl);
		$headers = explode("\r\n", trim($headers));
		$this->assertContains("HTTP/1.1", $headers[0]);
		curl_close($curl);
		// http version 1.0 case
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_NOBODY, 1);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		$headers = curl_exec($curl);
		$headers = explode("\r\n", trim($headers));
		$this->assertContains("HTTP/1.0", $headers[0]);
		curl_close($curl);
	}

	/**
	 * Option CURLOPT_HEADERFUNCTION set callback for response headers
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptHeaderFunction() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$headers = [];
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($ch, $header) use (&$headers) {
			$headers[] = trim($header);
			return strlen($header);
		});
		$content = curl_exec($curl);
		$this->assertSame('{"method":"GET","get":[],"post":[]}', $content);
		$expected_headers = [
			"HTTP/1.1 200 OK",
			"Host: 127.0.0.1:12345",
			"Connection: close",
			"X-Powered-By: PHP/5.5.31",
			"Content-type: text/html",
			""
		];
		$this->assertSame($expected_headers, $headers);
		curl_close($curl);
	}

	/**
	 * Option CURLOPT_CUSTOMREQUEST for make requests like DELETE, PUT
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptCustomReuest() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		$content = curl_exec($curl);
		$this->assertSame('{"method":"DELETE","get":[],"post":[]}', $content);
	}

	/**
	 * With option CURLINFO_HEADER_OUT curl_getinfo will contains request_header
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlInfoHeaderOut() {
		$server = new Testo\Server(EchoResponse::class);
		// with option
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
		$content = curl_exec($curl);
		$this->assertSame('{"method":"GET","get":[],"post":[]}', $content);
		$info = curl_getinfo($curl);
		$this->assertArrayHasKey("request_header", $info);
		$expected = "GET / HTTP/1.1\r\nHost: 127.0.0.1:12345\r\nAccept: */*\r\n\r\n";
		$this->assertSame($expected, curl_getinfo($curl, CURLINFO_HEADER_OUT));
		// without option
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLINFO_HEADER_OUT, 0);
		$content = curl_exec($curl);
		$this->assertSame('{"method":"GET","get":[],"post":[]}', $content);
		$info = curl_getinfo($curl);
		$this->assertArrayNotHasKey("request_header", $info);
		$this->assertSame(false, curl_getinfo($curl, CURLINFO_HEADER_OUT));
	}

	/**
	 * @todo
	 * Option CURLOPT_IPRESOLVE to use ip4 or ip6
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptIpResolve() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	}

	/**
	 * @todo
	 * Option CURLOPT_FOLLOWLOCATION to follow redirects
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptFollowLocation() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	}

	/**
	 * Option CURLOPT_HTTPGET resets method to GET
	 * @covers curl_setopt
	 * @covers Curl::setOpt
	 */
	public function testCurlOptHttpGet() {
		$server = new Testo\Server(EchoResponse::class);
		$curl = curl_init($server->getUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_HTTPGET, 1);
		$content = curl_exec($curl);
		$this->assertSame('{"method":"GET","get":[],"post":[]}', $content);
	}
}
