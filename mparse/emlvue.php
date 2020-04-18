<?php

class MySimpleMailParse
{
	protected	$fh,
			//	$headers = [],
				$boundaries = [],
				$sections = [];

	public function __construct ($fref)
	{
		$this->fh = fopen($fref, 'r');
		$this->parse();
		fclose($this->fh);
	}

	public function getHeaderValue ($header)
	{
		// last section should have the targeted headers
		$s = end($this->sections);
		if (empty($s['headers'][$header])) return null;
		return $s['headers'][$header];
	}

	public function body ($part=0)
	{
		$secn = 0;
		foreach ($this->sections as $i => $s)
		{
			if (empty($s['headers']['content-type'])) continue;
			$ct = $s['headers']['content-type'];
			if (strpos($ct,'text/plain') !== false) $secn = $i;
			if (strpos($ct,'text/html') !== false) {
				$secn = $i;
				break;
			}
		}
		$sect = $this->sections[$secn];
		$cte = empty($sect['headers']['content-transfer-encoding']) ? '' : strtolower($sect['headers']['content-transfer-encoding']);
		$pt = empty($sect['headers']['content-type']) ? false : strpos($sect['headers']['content-type'],'text/plain') !== false;
		$cis = $pt ? '<br>' : '';
		switch ($cte) {
			case 'quoted-printable':
				if ($pt) return nl2br(quoted_printable_decode(implode('',$sect['content'])));
				return quoted_printable_decode(implode($cis,$sect['content']));
			case 'base64':
				if ($pt) return nl2br(base64_decode(implode('',$sect['content'])));
				return base64_decode(implode($cis,$sect['content']));
			default:
				return implode($cis,$sect['content']);
		}
	}

	private function parse ($bound=false)
	{
		$section = ['headers'=>[],'boundary'=>'','content'=>'','zzz'=>''];
	//	$headers = [];
		$headone = false;
		$headstr = '';
		$qp = false;
		$lines = [];
		$lcnt = 0;
		while (($line = fgets($this->fh)) !== false) {
			$line = rtrim($line);
			if (!$headone) {
				// get the headers
				if (preg_match('#^\s+(.+)$#', $line, $mtch)) {
					$headstr .= ' '.$mtch[1];
				} else {
					if ($headstr) {
						if (preg_match('#^([^:]+): (.+)$#', $headstr, $mtch)) {
							$section['headers'][strtolower($mtch[1])] = $mtch[2];
						} else {
							$headstr = '';
							continue;
						}
					}
					if ($line === '') {	// end of headers
						$headone = true;
						// see if there is a boundrry
						if (!empty($section['headers']['content-type']) && preg_match('# boundary="?([^ ]+)"? ?#',$section['headers']['content-type'],$mtch)) {
							$section['boundary'] = $mtch[1];
							$this->boundaries[] = '--'.substr($section['boundary'],0,8);
						}
						// flag quoted-printable
						$qp = !empty($section['headers']['content-transfer-encoding']) && strtolower($section['headers']['content-transfer-encoding'])=='quoted-printable';
					} else {
						$headstr = $line;
					}
				}
			} else {
				// scan the body
				if ($bound || !!$section['boundary']) {
					if ((substr($line,0,2)=='--') && in_array(substr($line,0,10), $this->boundaries)) {
						$this->parse($bound || !!$section['boundary']);
					}
				}
				if (!in_array(substr($line,0,10), $this->boundaries)) {
					if ($qp && substr($line,-1)=='=') {
						$line = substr($line,0,-1);
					}
					$lines[] = $line;
				}
			}
			$lcnt++;
		}
		$section['content'] = $lines;
		$this->sections[] = $section;
	}

}

function my_mail_parse ($fref)
{
	$msmp = new MySimpleMailParse($fref);		//file_put_contents('MSMP.log',print_r($msmp,true));
//	echo'<xmp>';var_dump($msmp);echo'</xmp>';

	$head = '<style>.msmh_{font-weight:bold}</style>';
	$head .= '<div><span class="msmh_">Date:</span> '.$msmp->getHeaderValue('date').'</div>';
	$head .= '<div><span class="msmh_">From:</span> '.htmlspecialchars($msmp->getHeaderValue('from')).'</div>';
	$head .= '<div><span class="msmh_">To:</span> '.htmlspecialchars($msmp->getHeaderValue('to')).'</div>';
	$cc = $msmp->getHeaderValue('cc');
	if ($cc) {
		$head .= '<div><span class="msmh_">CC:</span> '.htmlspecialchars($cc).'</div>';
	}
	$head .= '<div><span class="msmh_">Subject:</span> '.htmlspecialchars($msmp->getHeaderValue('subject')).'</div>';
	$head .= '<hr>';


	$body = $msmp->body(1);
//	preg_match('#<img(.+)src#i',$body,$mtch);		echo'<xmp>';var_dump($mtch);echo'</xmp>';
//	preg_match('#<\s*img(.*?) src\s*=\s*(["\'])(.+?)\2#i',$body,$mtch);		echo'<xmp>';var_dump($mtch);echo'</xmp>';
	// twart getting remote images
	$body = preg_replace('#<\s*img(.*?) src\s*=\s*(["\'])(.+?)\2#i','<img$1 src="graphics/holder.gif"',$body);
//	preg_match_all('#\{([^}]*?)background-(.*?)url\s*\(\s*(["\'])(.+?)\3#i',$body,$mtch);		echo'<xmp>';var_dump($mtch);echo'</xmp>';
	$body = preg_replace('#url\s*\(\s*(["\']?)(.+?)\1?\)#i','url(graphics/holdery.gif)',$body);
	$body = preg_replace('#background\s*=\s*(["\'])(.+?)\1#i','background="graphics/holdery.gif"',$body);
	$body = preg_replace('#src\s*=\s*(["\'])http(.+?)\1#i','src="graphics/holdery.gif"',$body);
	$body = preg_replace('#srcset\s*=\s*(["\'])http(.+?)\1#i','srcset="graphics/holdery.gif"',$body);

/*
	$p = 0;
	$ha = [];
	while ($p = strpos($body, 'http', $p)) {
		$ha[] = substr($body, max(($p-30),0), 50);
		$p+=4;
	}
	$body .= '<xmp>' . implode("\n",$ha) . '</xmp>';
*/

	return $head.$body;
}
