<?php

namespace Curlyc\Blank;

use Testo\Responsible;

class EchoResponse implements Responsible {
	public function response() {
		$request = [
			"method" => $_SERVER['REQUEST_METHOD'],
			"get"    => $_GET,
			"post"   => $_POST,
		];
		return json_encode($request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}
}