<?php

class MySimpleMailParse
{
	protected $fh, $boundaries = [], $sections = [];

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
		$h = $s['headers'][$header];
		if (substr(ltrim($h,'" '),0,2)=='=?') $h = iconv_mime_decode($h);
		return $h;
	}

	public function getBody (&$ishtml, $part=0)
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
		$ishtml = strpos($ct,'text/html') !== false;
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

	public function getAttachment (&$type, &$name, $part=0, $noc=true)
	{
		$attn = 0;
		foreach ($this->sections as $i => $s)
		{
			if (empty($s['headers']['content-type'])) continue;
			$ct = $s['headers']['content-type'];
			if (strpos($s['headers']['content-disposition'] ?? '','attachment') !== false) {
				if ($attn == $part) {
					$cte = empty($s['headers']['content-transfer-encoding']) ? '' : strtolower($s['headers']['content-transfer-encoding']);
					$pt = empty($s['headers']['content-type']) ? false : strpos($s['headers']['content-type'],'text/plain') !== false;
					preg_match('#(.+);\s+name="([^"]+)"#', $s['headers']['content-type'], $matches);
					$type = $matches[1];
					$name = $matches[2];
					if ($noc) return true;

					$cis = $pt ? '<br>' : '';
					switch ($cte) {
						case 'quoted-printable':
							if ($pt) return nl2br(quoted_printable_decode(implode('',$s['content'])));
							return quoted_printable_decode(implode($cis,$s['content']));
						case 'base64':
							if ($pt) return nl2br(base64_decode(implode('',$s['content'])));
							return base64_decode(implode($cis,$s['content']));
						default:
							return implode($cis,$s['content']);
					}
					break;
				}
				$attn++;
			}
		}
		return false;
	}

	private function parse ($bound=false)
	{
		$section = ['headers'=>[],'boundary'=>'','content'=>''];
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

	$attachments = [];
	$attn = 0;
	$type = $name = $content = '';
	while ($content = $msmp->getAttachment($type, $name, $attn)) {
		//if ($content !== true) file_put_contents($name, $content);
		$attachments[$attn] = ['type'=>$type,'name'=>$name];
		$attn++;
	}

	// if a fetch/ajax call just send the attachment and exit
	$pinp = file_get_contents('php://input');
	if ($pinp) {
		$type = $name = $content = '';
		$jreq = json_decode($pinp, true);
		$content = $msmp->getAttachment($type, $name, $jreq['attn'], false);
		header('Content-Encoding: gzip');
		echo gzencode($content);
		flush();exit();
	}

	// form the email header: date,from,to,etc
	$head = '<style>.msmh_{font-weight:bold}</style>';
	$head .= '<div><span class="msmh_">Date:</span> '.$msmp->getHeaderValue('date').'</div>';
	$head .= '<div><span class="msmh_">From:</span> '.htmlspecialchars($msmp->getHeaderValue('from')).'</div>';
	$head .= '<div><span class="msmh_">To:</span> '.htmlspecialchars($msmp->getHeaderValue('to')).'</div>';
	$cc = $msmp->getHeaderValue('cc');
	if ($cc) {
		$head .= '<div><span class="msmh_">CC:</span> '.htmlspecialchars($cc).'</div>';
	}
	$head .= '<div><span class="msmh_">Subject:</span> '.htmlspecialchars($msmp->getHeaderValue('subject')).'</div>';
	if ($attachments) {
		$attlnks = '';
		foreach ($attachments as $k=>$v) {
			$attlnks .= '<a href="javascript:dnld_att('.$k.', \''.$v['name'].'\')" data-atype="'.$v['type'].'" data-aname="'.$v['name'].'">'.$v['name'].'</a> ';
		}
		$head .= '<div><span class="msmh_">Attachments:</span> '.$attlnks.'</div>';
	}
	$head .= '<hr>';

	// get the email body
	$ishtml = false;
	$body = $msmp->getBody($ishtml, 1);
/*
	// twart getting remote images, etc
	$body = preg_replace('#<\s*img(.*?) src\s*=\s*(["\'])(.+?)\2#i','<img$1 src="graphics/holder.gif"',$body);
	$body = preg_replace('#url\s*\(\s*(["\']?)(.+?)\1?\)#i','url(graphics/holdery.gif)',$body);
	$body = preg_replace('#background\s*=\s*(["\'])(.+?)\1#i','background="graphics/holdery.gif"',$body);
	$body = preg_replace('#src\s*=\s*(["\'])http(.+?)\1#i','src="graphics/holdery.gif"',$body);
	$body = preg_replace('#srcset\s*=\s*(["\'])http(.+?)\1#i','srcset="graphics/holdery.gif"',$body);
*/
/*
	$p = 0;
	$ha = [];
	while ($p = strpos($body, 'http', $p)) {
		$ha[] = substr($body, max(($p-30),0), 50);
		$p+=4;
	}
	$body .= '<xmp>' . implode("\n",$ha) . '</xmp>';
*/

	if ($ishtml) {
		// use washtml from roundcube
		include 'utils.php';
		include 'washtml.php';
		$oerl = error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);	// insulate the r-cube notices and warnings
		$washer = new rcube_washtml(['blocked_src'=>'graphics/holdery.gif','charset'=>'utf8']);
		$body = $washer->wash($body);
		error_reporting($oerl);
	}

	// add a script to manage attachment download
	$emlscript = <<< EOT
	function dnld_att (atn, atnm) {
		fetch('', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ attn: atn })})
		.then(resp => resp.blob())
		.then(data => {
			var url = window.URL || window.webkitURL;
			lnk = url.createObjectURL(data);
			var a = document.createElement("a");
			a.setAttribute("download", atnm);
			a.setAttribute("href", lnk);
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
		})
		.catch(e => alert(e));
	}
EOT;

	return $head.$body.'<script>'.$emlscript.'</script>';
}

