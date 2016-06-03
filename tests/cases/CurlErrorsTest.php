<?php

namespace Curlyc;

use Testo;

/**
 * Class CurlTest
 * @package Curlyc
 * @coversDefaultClass Curlyc/Curl
 * @backupStaticAttributes enabled
 */
class CurlErrorsTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @todo
	 * CURLE_SSL_CACERT
	 */
	public function testCurlESslCaCert() {
		$this->assertTrue(true);
	}
	
	/**
	 * @todo
	 * CURLE_SSL_CACERT_BADFILE
	 */
	public function testCurlESslCaCertBadFile() {
		$this->assertTrue(true);
	}
}
