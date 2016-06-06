<?php

namespace Curlyc;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

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
	private $request_header     = false;
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
	 * @param string $name
	 * @param array $arguments
	 * @throws \Exception
	 */
	public function __call($name, $arguments) {
		throw new \Exception("Not implemented yet");
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
			] + (!empty($this->options[CURLINFO_HEADER_OUT]) ? ["request_header" => $this->request_header] : []);
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
				case CURLINFO_HEADER_OUT:               return $this->request_header;
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
			return !$this->errno = CURLE_URL_MALFORMAT;
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
//			return !$this->errno = CURLE_COULDNT_CONNECT;
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
		$this->total_time = microtime(true);

		$client = $this->getClient();

		$data = [];
		$data['version'] = "1.1";
		if (!empty($this->options[CURLOPT_HTTP_VERSION]) && $this->options[CURLOPT_HTTP_VERSION] == CURL_HTTP_VERSION_1_0) {
			$data['version'] = "1.0";
		}
		$method = "GET";
		if (!empty($this->options[CURLOPT_CUSTOMREQUEST])) {
			$method = $this->options[CURLOPT_CUSTOMREQUEST];
		} elseif (
			(!empty($this->options[CURLOPT_POST]) && empty($this->options[CURLOPT_HTTPGET])) ||
			!empty($this->options[CURLOPT_POSTFIELDS])
		) {
			$method = "POST";
			if (!empty($this->options[CURLOPT_POSTFIELDS])) {
				if (is_array($this->options[CURLOPT_POSTFIELDS])) {
					$data["form_params"] = $this->options[CURLOPT_POSTFIELDS];
				} else {
					parse_str($this->options[CURLOPT_POSTFIELDS], $data["form_params"]);
				}
			}
		}
		if (!empty($this->options[CURLINFO_HEADER_OUT])) {
			$this->request_header = $this->getRequestHeader($method, $data['version'], $client->getConfig("headers"));
		}
				
		try {
			$res = $client->request($method, $this->url, $data);
		} catch (RequestException $e) {
			return !$this->errno = CURLE_OPERATION_TIMEOUTED;
		} catch (\Exception $e) {
			var_dump(get_class($e));
			var_dump($e->getMessage());
			return !$this->errno = 10001;
		}
		$headers = $this->getResponseHeaders($res);

		$this->setStats($res, $headers);

		$response = $res->getBody()->getContents();
		if (!empty($this->options[CURLOPT_HEADER])) {
			$response = $headers . $response;
		}
		return $response;
	}

	public function is_init() {
		if (!$this->init) {
			var_dump((new \Exception())->getTrace()[1]['function']);
			$function = (new \Exception())->getTrace()[1]['function'];
			throw new \Exception("$function(): supplied resource is not a valid cURL handle resource");
		}
	}

	/**
	 * @return array
	 */
	private function getHeaders() {
		$headers = [];
		// set headers
		if (!empty($this->options[CURLOPT_HTTPHEADER])) {
			foreach ($this->options[CURLOPT_HTTPHEADER] as $option) {
				list($key, $value) = explode(": ", $option);
				$headers[$key] = $value;
			}
		}
		// set user-agent
		if (!empty($this->options[CURLOPT_USERAGENT])) {
			$headers["User-Agent"] = $this->options[CURLOPT_USERAGENT];
		}
		if (!empty($this->options[CURLOPT_ENCODING])) {
			$headers["Accept-Encoding"] = $this->options[CURLOPT_ENCODING];
		}
		if (!empty($this->options[CURLOPT_USERPWD])) {
			$headers["Authorization"] = "Basic " . base64_encode($this->options[CURLOPT_USERPWD]);
		}
		return $headers;
	}

	/**
	 * @param ResponseInterface $res
	 * @return array
	 */
	private function getResponseHeaders(ResponseInterface $res) {
		$headers[] = implode(" ", [
			"HTTP/" . $res->getProtocolVersion(),
			$res->getStatusCode(),
			$res->getReasonPhrase(),
		]);
		foreach ($res->getHeaders() as $name => $values) {
			$headers[] = "$name: " . implode(", ", $values);
		}
		$headers[] = "\r\n";
		if (!empty($this->options[CURLOPT_HEADERFUNCTION])) {
			array_map(function($el) {
				$this->options[CURLOPT_HEADERFUNCTION]($this, $el);
			}, $headers);
		}
		return implode("\r\n", $headers);
	}

	/**
	 * @param string $method
	 * @param string $version
	 * @param array $headers
	 * @return string
	 */
	private function getRequestHeader($method, $version, array $headers) {
		$rheaders = [
			"$method / HTTP/$version",
			"Host: {$this->host}:{$this->primary_port}",
			"Accept: */*",
		];
		foreach ($headers as $k => $v) {
			$rheaders[] = "$k: $v";
		}
		return implode("\r\n", $rheaders) . "\r\n\r\n";
	}

	/**
	 * @param ResponseInterface $res
	 * @param string $headers
	 */
	private function setStats(ResponseInterface $res, $headers) {
		$this->content_type = $res->getHeaderLine("content-type");
		$this->http_code = $res->getStatusCode();
		$this->header_size = strlen($headers);
		$this->request_size;
		$this->connect_time;
		$this->pretransfer_time;
		$this->starttransfer_time;

		$this->size_download = strlen($res->getBody()->getSize());
		$this->total_time = microtime(true) - $this->total_time;
		$this->speed_download = $this->size_download / $this->total_time;
	}

	/**
	 * @return GuzzleHttp\Client
	 */
	private function getClient() {
		$config = [
			"connect_timeout" => isset($this->options[CURLOPT_CONNECTTIMEOUT]) ? $this->options[CURLOPT_CONNECTTIMEOUT] : 60,
			"timeout" => isset($this->options[CURLOPT_TIMEOUT]) ? $this->options[CURLOPT_TIMEOUT] : 60,
			"handler" => new GuzzleHttp\Handler\StreamHandler(),
			"headers" => $this->getHeaders(),
		];
		if (!isset($this->options[CURLOPT_ENCODING])) {
			$config["decode_content"] = false;
		}
		return new GuzzleHttp\Client($config);
	}
}
