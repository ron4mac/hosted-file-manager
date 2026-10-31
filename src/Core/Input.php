<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Core;

defined('EXEC_HFM') or die('Access denied');

class Input
{
	private array $get = [];
	private array $post = [];
	private array $file = [];
	private array $cookie = [];
	private array $server = [];

	public function __construct ()
	{
		$this->get = $this->sanitize($_GET);
		$this->post = $this->sanitize($_POST);
		$this->file = $_FILES;
		$this->cookie = $this->sanitize($_COOKIE, FILTER_UNSAFE_RAW);
		$this->server = $_SERVER;
	}

	private function sanitize (array $data, int $filter=FILTER_SANITIZE_SPECIAL_CHARS): array
	{
		$clean = [];
		foreach ($data as $key => $value) {
			$key = filter_var($key, $filter);
			if (is_array($value)) {
				$clean[$key] = $this->sanitize($value, $filter);
			} else {
				$clean[$key] = trim(filter_var($value, $filter));
			}
		}
		return $clean;
	}

	public function get (?string $key = null, mixed $default = null): mixed
	{
		if ($key === null) return $this->get;
		return $this->get[$key] ?? $default;
	}
	public function getHas (?string $key): bool
	{
		return array_key_exists($key, $this->get);
	}

	public function post (?string $key = null, mixed $default = null): mixed
	{
		if ($key === null) return $this->post;
		return $this->post[$key] ?? $default;
	}
	public function postHas (?string $key): bool
	{
		return array_key_exists($key, $this->post);
	}

	public function path (string $key, mixed $default = null): mixed
	{
		$value = $_GET[$key] ?? $_POST[$key] ?? null;
		if ($value === null) {
			return $default;
		}
		if (is_array($value)) {
			foreach ($value as $i=>$v) {
				$v = str_replace(chr(0), '', $v);
				$v = str_replace(['../', '..\\'], '', $v);
				$value[$i] = $v;
			}
		} else {
			$value = str_replace(chr(0), '', $value);
			$value = str_replace(['../', '..\\'], '', $value);
		}

		// Filter out control characters while preserving valid path characters
		return filter_var($value, FILTER_DEFAULT, FILTER_FLAG_STRIP_LOW | (is_array($value)?FILTER_REQUIRE_ARRAY:0));
	}
	public function pathHas (?string $key): bool
	{
		return array_key_exists($key, $this->get) || array_key_exists($key, $this->post);
	}

	public function raw (string $key, mixed $default = null): mixed
	{
		return $_POST[$key] ?? $_GET[$key] ?? $default;
	}

	public function file (string $key): mixed
	{
		if ($key === null) return $this->file;
		return $this->file[$key] ?? null;
	}

	public function fileHas ($key)
	{
		return array_key_exists($key, $this->file) && $this->file[$key]['error'] !== UPLOAD_ERR_NO_FILE;
	}

	public function cookie (?string $key = null, mixed $default = null): mixed
	{
		if ($key === null) return $this->cookie;
		return $this->cookie[$key] ?? $default;
	}
	public function cookieHas (?string $key): bool
	{
		return array_key_exists($key, $this->cookie);
	}
	public function setCookie ($key, $value, $expireSeconds = 3600, $path = '/')
	{
		return setcookie($key, $value, time() + $expireSeconds, $path);
	}
	public function deleteCookie ($key, $path = '/')
	{
		unset($_COOKIE[$key]);
		unset($this->cookie[$key]);
		return setcookie($key, '', time() - 3600, $path);
	}

	public function server (?string $key = null, mixed $default = null): mixed
	{
		if ($key === null) return $this->server;
		return $this->server[$key] ?? $default;
	}

	public function all (?string $key = null, mixed $default = null): mixed
	{
		$all = array_merge($this->cookie, $this->get, $this->post);
		if ($key === null) return $all;
		return $all[$key] ?? $default;
	}

	// function that can be used to rearrange the files arrays
	private function rearrange ($arr): array
	{
		$new = [];
		foreach ($arr as $key => $all) {
			foreach ($all as $i => $val) {
				$new[$i][$key] = $val;
			}
		}
		return $new;
	}

}
