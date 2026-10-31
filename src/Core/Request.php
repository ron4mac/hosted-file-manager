<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Core;

defined('EXEC_HFM') or die('Access denied');

class Request
{
	private string $uri;
	private string $method;

	public function __construct ()
	{
		$this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		$this->uri = $this->parseUri();
	}

	private function parseUri (): string
	{
		// 1. Check if PHP populated PATH_INFO directly
		if (!empty($_SERVER['PATH_INFO'])) {
			$uri = $_SERVER['PATH_INFO'];
		} else {
			// 2. Fallback: Parse REQUEST_URI manually
			$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
			$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
			// Remove the script path from the URI
			if (stripos($uri, $scriptName) === 0) {
				$uri = substr($uri, strlen($scriptName));
			} else {
				// If accessed without index.php explicitly typed
				$baseDir = dirname($scriptName);
				if ($baseDir !== '/' && $baseDir !== '\\' && stripos($uri, $baseDir) === 0) {
					$uri = substr($uri, strlen($baseDir));
				}
			}
		}
		$uri = trim($uri, '/');
		return $uri ?: '/';
	}

	public function getUri (): string
	{
		return $this->uri;
	}

	public function getMethod (): string
	{
		return $this->method;
	}

	public function getBaseUrl (): string
	{
		$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
		$baseDir = dirname($scriptName);
		return ($baseDir === '/' || $baseDir === '\\') ? '' : $baseDir;
	}

}