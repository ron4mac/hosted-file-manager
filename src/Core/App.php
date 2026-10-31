<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Core;

defined('EXEC_HFM') or die('Access denied');

class App
{
	private array $routes = [];
	private Cfg $cfg;
	private Input $input;
	private Request $request;

	public function __construct ()
	{
		$this->cfg = new Cfg();
		$input = new Input();
		$this->input = $input;
		$this->request = new Request();
		$cooknam = (new Cfg())->val('cooknam');
		// establish (or get) a base directory path
		if ($input->getHas('bgn')) {
			$basDir = $input->get('bgn');
			$input->setCookie($cooknam,convert_uuencode($basDir));
		} else {
			$basDir = $input->cookieHas($cooknam) ? convert_uudecode($input->cookie($cooknam)) : false;
			if (!$basDir) {
				$basDir = dirname($input->server('DOCUMENT_ROOT'));
				$input->setCookie($cooknam,convert_uuencode($basDir));
			}
		}
		define('BASE_DIR', $basDir.'/');
		define('BASE_URL', $this->request->getBaseUrl());
	}

	public function get (string $path, array $handler): void
	{
		$this->routes['GET'][trim($path, '/')] = $handler;
	}

	public function post (string $path, array $handler): void
	{
		$this->routes['POST'][trim($path, '/')] = $handler;
	}

	public function run (): void
	{
		$method = $this->request->getMethod();
		$uri = $this->request->getUri();
		[$path,$meth] = array_pad(explode('/', $uri === '/' ? '' : $uri), 2, null);
		$z = 0;
		if (isset($this->routes[$method][$path])) {
			$z = 1;
			[$controllerClass, $action] = $this->routes[$method][$path];
			$action = $action ?: $meth;

			if (class_exists($controllerClass)) {
				$z = 2;
				$controller = new $controllerClass($this->input, $this->cfg);
				if (method_exists($controller, $action)) {
					$z = 3;
					// Pass Input instance directly to controller action
					$controller->$action($this->input, $this->cfg);
					return;
				}
			}
		}

		http_response_code(404);
		echo "404 Not Found";
		echo'<xmp>';var_dump('z '.$z.$action,'M '.$method,' U '.$uri,' P '.$path,$this->routes);echo'</xmp>';
	}

	public static function render (string $view, array $data = []): void
	{
		extract($data);
		$viewFile = __DIR__ . '/../Views/' . $view . '.php';
		if (file_exists($viewFile)) {
			require $viewFile;
		} else {
			echo "View file [{$view}] not found.";
		}
	}
}