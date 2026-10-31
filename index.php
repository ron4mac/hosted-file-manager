<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
define('EXEC_HFM', true);

spl_autoload_register(function ($class) {
	$prefix = 'Rjc\\Hfm\\';
	// Changed from __DIR__ . '/../app/' to __DIR__ . '/app/'
	$baseDir = __DIR__ . '/src/';

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}

	$relativeClass = substr($class, $len);
	$file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

	if (file_exists($file)) {
		require $file;
	}
});

use Rjc\Hfm\Core\App;
use Rjc\Hfm\Controllers\HomeController;
use Rjc\Hfm\Controllers\ActionController;
use Rjc\Hfm\Controllers\FileController;
use Rjc\Hfm\Controllers\OtherController;

$app = new App();

$app->get('/', [HomeController::class, 'index']);
$app->post('/', [HomeController::class, 'index']);
$app->get('/index.php', [HomeController::class, 'index']);
$app->post('/index.php', [HomeController::class, 'index']);
$app->post('/submit', [HomeController::class, 'submit']);
$app->post('/fmxjx', [ActionController::class, 'action']);
$app->get('file', [FileController::class, '']);
$app->post('file', [FileController::class, '']);
$app->get('other', [OtherController::class, '']);
$app->post('other', [OtherController::class, '']);

$app->run();