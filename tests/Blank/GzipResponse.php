<?php

namespace Curlyc\Blank;

class GzipResponse extends EchoResponse {
	public function response() {
		header("Content-Encoding: gzip");
		$content = gzcompress(parent::response());
//		header("Content-Length: " . mb_strlen($content));
		return $content;
	}
}