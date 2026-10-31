<?php
/**
* @package		hosted-file-manager
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		4.0.0
*/
namespace Rjc\Hfm\Core;

defined('EXEC_HFM') or die('Access denied');

class Cfg extends CfgLoader
{
	public function __construct ()
	{
		$this->loadCfgOptions(__DIR__.'/../../cfg.php');
		$this->cfg['jsver'] = $this->cfg['dev_mode'] ? '' : '.min';

		$rmtuser = getenv('REMOTE_USER');
		$this->cfg['cooknam'] .= ($rmtuser ? "_$rmtuser" : '');
	}

	public function val (string $valn, mixed $dflt=null): mixed
	{
		return $this->cfg[$valn] ?? $dflt;
	}

	public function all_val (): array
	{
		return $this->cfg;
	}
}

//include_once __DIR__.'/../../cfg.php';

abstract class CfgLoader {
	protected array $cfg = [
		'fmxVersion' => '4.0.0 - September 2026',
		// Developer mode
		'dev_mode' => false,
		'jsver' => '',
		'cooknam' => 'fil_vew',
		// the version of ace editor to be used
		'aceBase' => '//cdnjs.cloudflare.com/ajax/libs/ace/1.37.1/',
		// Specify the theme to use with Ace editor
		// If theme name starts with /, it is a local theme file in js/ace/
		//'acetheme' => '/rjcode',
		// Other wise it is a theme included in the Ace package
		// If no theme is entered here, the editor will provide a dropdown selection list
		//   - hover over the list name to see the string that should be entered below
		'acetheme' => 'sqlserver',
		// link to font awesome
		'fontawsm' => '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'
	];

	/**
	 * Includes a PHP file and imports its global variables into the class properties.
	 */
	protected function loadCfgOptions (string $fPath): void
	{
		if (!file_exists($fPath)) return;

		// Isolate the include inside a closure or method scope so local variables 
		// don't pollute the broader class scope, but global variables/returns are captured.
		$data = (function($_pth) {
			include $_pth;
			return get_defined_vars();
		})($fPath);

		// Assign the extracted variables
		foreach ($data as $key => $value) {
			if ($key=='_pth') continue;
			$this->cfg[$key] = $value;
		}
	}
}