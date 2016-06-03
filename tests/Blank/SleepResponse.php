<?php

namespace Curlyc\Blank;

class SleepResponse extends EchoResponse {
	public function response() {
		sleep(isset($_GET["sleep"]) ? (int) $_GET["sleep"] : 3);
		return parent::response();
	}
}