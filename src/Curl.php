<?php

namespace Curlyc;

use GuzzleHttp;

/**
 * Class Curlyk
 * @package Curlyc
 */
class Curl {

	const PROTOCOLS = [
		"dict"   => 0,
		"file"   => 0,
		"ftp"    => 0,
		"ftps"   => 0,
		"gopher" => 0,
		"http"   => 1,
		"https"  => 0,
		"imap"   => 0,
		"imaps"  => 0,
		"pop3"   => 0,
		"pop3s"  => 0,
		"rtsp"   => 0,
		"smb"    => 0,
		"smbs"   => 0,
		"smtp"   => 0,
		"smtps"  => 0,
		"telnet" => 0,
		"tftp"   => 0,
	];

	private $init    = false;
	private $errno   = 0;
	private $options = [];

	private $host = "";
	//<editor-fold desc="..fields..">
	private $url     = ""; // CURLOPT_URL
	private $scheme;
	private $content_type;
	private $http_code          = 0;
	private $header_size        = 0;
	private $request_size       = 0;
	private $filetime           = 0;
	private $ssl_verify_result  = 0;
	private $redirect_count     = 0;
	private $total_time         = 0.0;
	private $namelookup_time    = 0.0;
	private $connect_time       = 0.0;
	private $pretransfer_time   = 0.0;
	private $size_upload        = 0.0;
	private $size_download      = 0.0;
	private $speed_download     = 0.0;
	private $speed_upload       = 0.0;
	private $download_content_length    = -1.0;
	private $upload_content_length      = -1.0;
	private $starttransfer_time         = 0.0;
	private $redirect_time      = 0.0;
	private $redirect_url       = "";
	private $primary_ip         = "";
	private $certinfo           = [];
	private $primary_port       = 0;
	private $local_ip           = "";
	private $local_port         = 0;
	//</editor-fold>
	
	/**
	 * @param string $url
	 */
	public function __construct($url) {
		if ($url) {
			$this->url = (string) $url;
		}
		$this->options[CURLOPT_URL] = $url;
		$this->init = true;
	}

	/**
	 * @todo
	 */
	public function close() {
		$this->init = false;
	}

	/**
	 * @return int
	 */
	public function errno() {
		return $this->errno;
	}

	/**
	 * @return string
	 */
	public function error() {
		return curl_strerror($this->errno);
	}

	/**
	 * @return bool|string
	 */
	public function exec() {
		if (!$this->initate()) {
			return false;
		}
		$res = $this->get();
		if (!empty($this->options[CURLOPT_RETURNTRANSFER])) {
			return $res;
		}
		echo $res;
		return true;
	}

	/**
	 * @todo
	 * @param int $opt
	 * @return mixed
	 */
	public function getInfo($opt = null) {
		if (!$opt) {
			return [
				"url"                       => $this->url,
				"content_type"              => $this->content_type,
				"http_code"                 => $this->http_code,
				"header_size"               => $this->header_size,
				"request_size"              => $this->request_size,
				"filetime"                  => $this->filetime,
				"ssl_verify_result"         => $this->ssl_verify_result,
				"redirect_count"            => $this->redirect_count,
				"total_time"                => $this->total_time,
				"namelookup_time"           => $this->namelookup_time,
				"connect_time"              => $this->connect_time,
				"pretransfer_time"          => $this->pretransfer_time,
				"size_upload"               => $this->size_upload,
				"size_download"             => $this->size_download,
				"speed_download"            => $this->speed_download,
				"speed_upload"              => $this->speed_upload,
				"download_content_length"   => $this->download_content_length,//
				"upload_content_length"     => $this->upload_content_length,
				"starttransfer_time"        => $this->starttransfer_time,
				"redirect_time"             => $this->redirect_time,
				"redirect_url"              => $this->redirect_url,
				"primary_ip"                => $this->primary_ip,
				"certinfo"                  => $this->certinfo,
				"primary_port"              => $this->primary_port,
				"local_ip"                  => $this->local_ip,
				"local_port"                => $this->local_port,
			];
		} else {
			switch ($opt) {
				case CURLINFO_EFFECTIVE_URL:            return $this->url;
				case CURLINFO_CONTENT_TYPE:             return $this->content_type;
				case CURLINFO_HTTP_CODE:                return $this->http_code;
				case CURLINFO_HEADER_SIZE:              return $this->header_size;
				case CURLINFO_REQUEST_SIZE:             return $this->request_size;
				case CURLINFO_FILETIME:                 return $this->filetime;
				case CURLINFO_SSL_VERIFYRESULT:         return $this->ssl_verify_result;
				case CURLINFO_REDIRECT_COUNT:           return $this->redirect_count;
				case CURLINFO_TOTAL_TIME:               return $this->total_time;
				case CURLINFO_NAMELOOKUP_TIME:          return $this->namelookup_time;
				case CURLINFO_CONNECT_TIME:             return $this->connect_time;
				case CURLINFO_PRETRANSFER_TIME:         return $this->pretransfer_time;
				case CURLINFO_SIZE_UPLOAD:              return $this->size_upload;
				case CURLINFO_SIZE_DOWNLOAD:            return $this->size_download;
				case CURLINFO_SPEED_DOWNLOAD:           return $this->speed_download;
				case CURLINFO_SPEED_UPLOAD:             return $this->speed_upload;
				case CURLINFO_CONTENT_LENGTH_DOWNLOAD:  return $this->download_content_length;
				case CURLINFO_CONTENT_LENGTH_UPLOAD:    return $this->upload_content_length;
				case CURLINFO_STARTTRANSFER_TIME:       return $this->starttransfer_time;
				case CURLINFO_REDIRECT_TIME:            return $this->redirect_time;
				case CURLINFO_REDIRECT_URL:             return $this->redirect_url;
				case CURLINFO_PRIMARY_IP:               return $this->primary_ip;
				case CURLINFO_PRIVATE:                  return $this->certinfo;
				case CURLINFO_PRIMARY_PORT:             return $this->primary_port;
				case CURLINFO_LOCAL_IP:                 return $this->local_ip;
				case CURLINFO_LOCAL_PORT:               return $this->local_port;
				case CURLINFO_HEADER_OUT:               return false;
			}
		}
		return false;
	}

	/**
	 * @todo
	 */
	public function reset() {
		$this->url = "";
	}

	/**
	 * @todo initate options
	 * @param int $option
	 * @param mixed $value
	 * @return bool
	 */
	public function setOpt($option, $value) {
		$this->options[$option] = $value;
		return true;
	}

	/**
	 * @param array $options
	 * @return bool
	 */
	public function setOptArray(array $options) {
		foreach ($options as $option => $value) {
			if (!$this->setOpt($option, $value)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @return Curl
	 */
	public function __clone() {
		return new $this;
	}

	/**
	 * @todo
	 * @return bool
	 */
	private function initate() {
		if (!$this->url) {
			return false;
		}
		$parts = parse_url($this->url);
		// default scheme is http
		if (!isset($parts["scheme"])) {
			$this->url = "http://{$this->url}";
			return $this->initate();
		}
		// check protocol support
		$p = self::PROTOCOLS;
		if (!isset($p[$parts["scheme"]]) || !$p[$parts["scheme"]]) {
			return !$this->errno = CURLE_UNSUPPORTED_PROTOCOL;
		}
		$this->scheme = $parts["scheme"];
		// check host
		if (!isset($parts["host"])) {
			return !$this->errno = CURLE_URL_MALFORMAT;
		}
		$this->host = $parts["host"];
		// check port
		if (isset($parts["port"])) {
			$this->primary_port = $parts["port"];
		} else {
			if ($this->scheme == "https") {
				$this->primary_port = 443;
				$this->host = "ssl://{$this->primary_port}";
			} elseif ($this->scheme == "http") {
				$this->primary_port = 80;
				$this->host = "tcp://{$this->primary_port}";
			}
		}
		// time for namelookup
		$start = microtime(true);
		$ip = gethostbyname($parts["host"]);
		$this->namelookup_time = microtime(true) - $start;
		if ($ip == $parts["host"]) {
			return !$this->errno = CURLE_COULDNT_CONNECT;
		}
		$this->primary_ip = $ip;
		$this->local_ip = $ip;
		return true;
	}

	/**
	 * @todo
	 * @return string
	 */
	private function get() {
		$start = microtime(true);
		
		$client = new GuzzleHttp\Client(['handler' => new GuzzleHttp\Handler\StreamHandler()]);
		$options = [];
		$res = $client->request('GET', $this->url, $options);
		$headers = "";
		foreach ($res->getHeaders() as $name => $values) {
			$headers .= $name . ": " . implode(", ", $values) . PHP_EOL;
		}
		$this->content_type = $res->getHeaderLine("content-type");
		$this->http_code = $res->getStatusCode();
		$this->header_size = strlen($headers);
		$this->request_size;
		$this->connect_time;
		$this->pretransfer_time;
		$this->starttransfer_time;

		$this->size_download = strlen($res->getBody()->getSize());
		$this->total_time = microtime(true) - $start;
		$this->speed_download = $this->size_download / $this->total_time;

		return $res->getBody()->getContents();
	}

	public function is_init() {
		if (!$this->init) {
			var_dump((new \Exception())->getTrace()[1]['function']);
			$function = (new \Exception())->getTrace()[1]['function'];
			throw new \Exception("$function(): supplied resource is not a valid cURL handle resource");
		}
	}
}
