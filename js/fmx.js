/* jshint unused: true, esnext:false, esversion: 11 */
/* globals fmx_AJ, _rj, RJ_DlogMgr, rjOpenDlg, rjHtmlElement, curDir, fmx_ui, fmx_appPath, H5uSetup, upload_winpop, ctxPrf, URLSearchParams */
/* exported refreshFilstO, allSelect, selectionAction, fils2up, ctxmenus */
'use strict';
function refreshFilst () {
	window.location = window.location.href.split('#')[0];
}

function refreshFilstO (so) {
	var wlp = window.location.href.split('#')[0].split('?');
	if (wlp[1]) {
		window.location = wlp[0] + '?' + wlp[1].split('&')[0] + '&O=' + so;
	} else {
		window.location = wlp[0] + '?O=' + so;
	}
}

const form2string = (fmnm) => {
	let fmd = new FormData(document.forms[fmnm]);
	let usp = new URLSearchParams(fmd);
	return usp.toString();
};

const toFormData = (obj) => {
	const formData = new FormData();
	Object.keys(obj).forEach(key => {
		if (typeof obj[key] !== 'object') formData.append(key, obj[key]);
		else formData.append(key, JSON.stringify(obj[key]));
	});
	return formData;
};

function postAction (act, parms={}, cb=()=>{}, json=false) {
	if (typeof parms === 'object') {
		if (!(parms instanceof FormData)) parms = toFormData(parms);
	} else if (typeof parms === 'string') {
		parms = new URLSearchParams(parms);
	}
	if (act) parms.set('act', act);

	fetch(fmx_AJ, {method:'POST',body:parms})
	.then(resp => { if (!resp.ok) throw new Error('Network response was not OK'); if (json) return resp.json(); else return resp.text() })
	.then(data => cb(data))
	.catch(err => alert(err));
}

function postAndRefresh (parms) {
	postAction(null, parms, (data) => { if (data) alert(data); else refreshFilst() });
}

function postFormAndRefresh (act) {
	let parms = new FormData(document.forms.filst);
	parms.set('act', act);
	postAndRefresh(parms);
}

/*
function load_scripts(elmt,_cb) {

	if (!elmt) return;

	var scripts = elmt.getElementsByTagName('script');
	if (!scripts) return;

	var file = null;
	var fileref = null;
	for (var i = 0; i < scripts.length; i++) {
		file = scripts[i].getAttribute('src');
		if (file) {
			fileref = document.createElement('script');
			fileref.setAttribute('type', 'text/javascript');
		//	fileref.setAttribute('src', file);
			fileref.async = true;
			fileref.onload = _cb;	//() => _cb();
			fileref.setAttribute('src', file);
			document.getElementsByTagName('head').item(0).appendChild(fileref);
		} else {
			let jsx = scripts[i].innerText;
			window['eval'].call(window, jsx);
		//	eval(jsx);
		}
	}
}*/

var aMsgDlg = {
	title: 'Message:',
	cselect: '#aMsgDlog',
	hasX: true
};

var aSchDlg = {
	cselect: '#aSchDlog',
	hasX: true,
	buttons: {
		search: {text: 'Search'}
	}
};

var fRenDlg = {
	id: 4,
	title: 'Rename...',
	cselect: '#fRenDlog',
	modal: true,
	hasX: true,
	buttons: {
		rename: {text: 'Rename'}
	}
};

var fNamDlg = {
	cselect: '#fNamDlog',
	hasX: true,
	buttons: {
		create: {text: 'Create'}
	}
};

/*
var upLoad = {
	title: 'Upload files ...',
	cselect: '#upload',
	hasX: true,
	nobuttons: {
		create: {text: 'Create'}
	}
};*/

// file upload methods
var upldAction = {
	// HTML5 w/progress in a popup window
	H5w: () => popUp(fmx_appPath+'filupld5.php'),
	// HTML5 w/progress in a div overlay
	H5o: (cbf) => RJ_DlogMgr.fetchDlog(fmx_appPath+'filupld5.php?o=1', {title:'Upload files ...', ready: ()=>{H5uSetup();if (cbf) cbf();}}),
	// legacy HTML in a popup window
	L4w: () => popUp(fmx_appPath+'filupld.php'),
	// legacy HTML in a div overlay
	L4o: () => RJ_DlogMgr.fetchDlog(fmx_appPath+'filupld.php?o=1', {title:'Upload files ...'})
	};

function downloadFile (A, cdl, asf) {
	var dlURL = fmx_appPath+'fildnld.php?fle=' + encodeURI(A) + (cdl ? '&tcdl=1&rad=Y' : '') + (asf ? ('&asf='+asf) : '');
	var dlframe = document.createElement('iframe');
	// set source to desired file
	dlframe.src = dlURL;
	// This makes the IFRAME invisible to the user.
	dlframe.style.display = 'none';
	// Add the IFRAME to the page.  This will trigger the download
	document.body.appendChild(dlframe);
}

function makeFileList (fstr, htm) {
	var sep = htm ? '<br />' : ' ';
	var rslt = '';
	var itm, itms = fstr.split('\u0000');
	var fpts, fdr, fpt, fpa, cdl = encodeURIComponent(curDir).length;
	for (itm in itms) {
		fpts = itms[itm].split('&');
		fdr = '';
		for (fpt in fpts) {
			fpa = fpts[fpt].split('=');
			if (fpt*1===0) { fdr = fpa[1].substr(cdl); }
			else { rslt += fdr + '%2F' + fpa[1] + sep; }
		}
	}
	return rslt.replace(/%2F/g,'/');
}

function doesSupportAjaxUploadWithProgress () {

	function supportFileAPI() {
		return (typeof window.FileList !== 'undefined');
	}

	function supportAjaxUploadProgressEvents() {
		var xhr = new XMLHttpRequest();
		return !! (xhr && (('upload' in xhr) && ('onprogress' in xhr.upload)));
	}

	return supportFileAPI() && supportAjaxUploadProgressEvents();
}

function pop (url, h1, w1) {
	if (w1>screen.availWidth) w1 = screen.availWidth;
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon='toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width='+w1+',height='+h1+',left='+w2+',top='+h2;
	return window.open(url, '', wcon);
}

function popPost (url, data, name, h1, w1) {
	if (w1>screen.availWidth) w1 = screen.availWidth;
	let flds = '', key;
	for (key in data) { flds += '<input type="hidden" name="'+key+'" value="'+data[key]+'" />'; }
	let ppf = rjHtmlElement('form', {action: url, method: 'post', target: name}, flds);
	document.body.appendChild(ppf);
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon='toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width='+w1+',height='+h1+',left='+w2+',top='+h2;
	window.open('', name, wcon).focus();
	ppf.submit();
}

function popUp (url) {
	pop(url,240,416);
}

function doMenuAction (cmd,evt) {
	var slctd = document.querySelectorAll('.fsel:checked'),
		curFref = slctd[0]?.parentElement.parentElement.dataset.fref,
		scnt = slctd.length,
		parms, curfn, trmFrm, destfn,
		oneItem = () => { if (!scnt) { alert('An item needs to be selected'); } else if (scnt>1) { alert('Please select only one item.'); } else { return true; } return false; },
		hasSome = () => { if (scnt) { return true; } alert('Some items need to be selected'); return false; };
	switch (cmd) {
	case 'cppa':
		if (scnt) {
			sessionStorage.fmx_cppa = form2string('filst');
			show_mcount('cppaMenu', sessionStorage.fmx_cppa);
		} else if (sessionStorage.fmx_cppa) {
			postAndRefresh('act=cppa&todr='+encodeURIComponent(curDir)+'&'+sessionStorage.fmx_cppa);
		}
		break;
	case 'trsh':
		if (hasSome() && ((scnt==1) || confirm('You have multiple files selected. Are you sure you want to trash ALL the selected files?'))) {
			postFormAndRefresh('trsh');
		}
		break;
	case 'mpty':
		postAndRefresh('act=mpty');
		break;
	case 'delf':
		if (hasSome() && ((scnt==1) || confirm('You have multiple files selected. Are you sure you want to delete ALL the selected files?'))) {
			postFormAndRefresh('delf');
		}
		break;
	case 'tvew':
		window.location = '?dir=tmp/Trash';
		break;
	case 'dnld':
		if (hasSome()) {
			let dspin = document.querySelector('div.dnldprg');
			dspin.style.display = 'inline';
			parms = new FormData(document.forms.filst);
			parms.set('act','dnld');
			postAction(null, parms, (data) => {
				dspin.style.display = 'none';
				if (data) { downloadFile(data.fpth,data.rad=='Y',false); }
				else { alert('download not available'); }
			}, true);
		}
		break;
	case 'dupl':
		if (oneItem()) {
			parms = {
				act: 'dupl',
				fref: curDir + curFref
				};
			postAndRefresh(parms);
		}
		break;
	case 'mark':
		parms = (sessionStorage.fmx_mrkd === undefined) ? '' : (sessionStorage.fmx_mrkd+'\u0000');
		if (scnt) {
			sessionStorage.fmx_mrkd = parms + form2string('filst');
			bump_mcount('markMenu', scnt);
		}
		break;
	case 'mmiz':
		if (oneItem()) {
			var fn = curFref;
			RJ_DlogMgr.fetchDlog('jsmini.php?f='+encodeURIComponent(fn), {title:'Minimize javascript file...'});
		}
		break;
	case 'mvto':
		if (scnt) {
			sessionStorage.fmx_mvto = form2string('filst');
			show_mcount('mvtoMenu', sessionStorage.fmx_mvto);
		} else {
			if (!sessionStorage.fmx_mvto) {
				alert('Nothing previously selected to move');
				break;
			}
			parms = sessionStorage.fmx_mvto;
			sessionStorage.removeItem('fmx_mvto');
			postAndRefresh('act=mvto&todr='+encodeURIComponent(curDir)+'&'+parms);
		}
		break;
	case 'nfle':
	case 'nfld':
		fNamDlg.title = cmd=='nfle'?'New File':'New Folder';
		rjOpenDlg(evt, fNamDlg, {
			fattrs:{'input[name="act"]|value':cmd},
			cb: (act, fd) => {
				if (act != 'create') return;
				let fnm = fd.get('fref').trim();
				if (!fnm) { alert('Please enter a valid name'); return false; }
				let parms = {
					act: fd.get('act'),
					fref: curDir + fnm
					};
				postAndRefresh(parms);
			}
		});
		break;
	case 'refr':
		refreshFilst();
		break;
	case 'rnam':
		if (oneItem())  {
			curfn = curFref;
			doRenameFile(curfn, evt);
		}
		break;
	case 'srhf':
	case 'srhc':
		var strm = sessionStorage.fmx_strmf;
		if (cmd=='srhc') {
			strm = sessionStorage.fmx_strmc;
			aSchDlg.title = 'Search files for content...';
		} else {
			aSchDlg.title = 'Search for file named like...';
		}
		rjOpenDlg(evt, aSchDlg, {
			fattrs:{'input[name="cmd"]|value':cmd, 'input[name="sterm"]|value':strm},
			cb: (act, fd) => {
				if (act != 'search') return;
				let trm = fd.get('sterm').trim();
				if (!trm) { alert('Please enter a valid search term'); return false; }
				let	cmd = fd.get('cmd'),
					trmFrm = document.forms.cliterm;
				if (cmd==='srhf') {
					sessionStorage.fmx_strmf = trm;
					trmFrm.cmdlin.value = 'find ./ -name '+trm+' -ls';
				} else {
					sessionStorage.fmx_strmc = trm;
					trmFrm.cmdlin.value = 'grep -R -I '+trm+' *';
				}
				trmFrm.submit();
			}
		});
		break;
	case 'turl':
		// curl a file to a URL
		if (oneItem()) {
			RJ_DlogMgr.fetchDlog(fmx_appPath+'filcurlm.php?t=1', {title:'Send file to URL ...'});
		}
		break;
	case 'furl':
		// curl a file from a URL
		RJ_DlogMgr.fetchDlog(fmx_appPath+'filcurlm.php', {title:'Get file from URL ...'});
		break;
	case 'mdya':
		pop(fmx_appPath+'media.php?sd=/'+sessionStorage.fmx_curD,screen.availHeight,1200);
		break;
	case 'upld':
//		sessionStorage.fmx_curD = curDir;
		if (doesSupportAjaxUploadWithProgress()) {
			if (upload_winpop) {
				upldAction.H5w();
			} else {
				upldAction.H5o();
			}
		} else {
			if (upload_winpop) {
				upldAction.L4w();
			} else {
				upldAction.L4o();
			}
		}
		break;
	case 'uzip':
	case 'zip':
	case 'tarz':
	case 'utrz':
		if (oneItem()) {
			curfn = /*curDir+*/curFref;
			trmFrm = document.forms.cliterm;
			if (cmd=='zip') {
				var zcmd = 'zip ';
				if (slctd[0].parentElement.nextElementSibling.classList.contains('foldCtxt')) { zcmd += '-r '; }
				destfn = curfn.replace(/\s/g,'_');
				trmFrm.cmdlin.value = zcmd+destfn+'.zip "'+curfn+'"';
				if (evt.shiftKey) {
					let xyz = prompt('COMMAND:',trmFrm.cmdlin.value+' -x "*/sv_*" -x "*/.git*"');
					if (xyz) { trmFrm.cmdlin.value = xyz; }
					else break;
				}
			} else if (cmd=='uzip') {
				trmFrm.cmdlin.value = 'unzip "'+curfn+'"';
			} else if (cmd=='tarz') {
				destfn = curfn.replace(/\s/g,'_');
				trmFrm.cmdlin.value = 'tar -czf '+destfn+'.tgz "'+curfn+'"';
			} else if (cmd=='utrz') {
				trmFrm.cmdlin.value = 'tar -xzf "'+curfn+'"';
			}
			trmFrm.submit();
		}
		break;
	case 'mgzp':
		if (oneItem()) {
			curfn = /*curDir+*/curFref;
			var fileoi = encodeURIComponent(curDir+curfn);
			doManageZip(fileoi);
		}
		break;
	case 'webv':
		if (oneItem()) {
			curfn = curFref;
			if (evt.shiftKey) {
				let xyz = prompt('URL:',curfn);
				if (xyz) { curfn = xyz; }
				else break;
			}
			var wPath = ctxPrf+curDir.slice(curDir.search('/'));
			pop(wPath+curfn,screen.availHeight,0.6*screen.availWidth);
		}
		break;
	case 'gitr':
		if (oneItem()) {
			if ('.git' == curFref) {
				parms = '?dir='+curDir;
				pop(fmx_appPath+'gitter.php'+parms,screen.availHeight,1200);
			} else {
				alert('Please select a \'.git\' folder');
			}
		}
		break;
	case 'jxtr':
		if (oneItem()) {
			curfn = curDir+curFref;
			var m = curfn.match(/([^\/\\]+)\.(\w+)$/);
			if (m && m[2] == 'xml') {
				parms = {act: 'jxtr', fref: curfn, dir: curDir};
				postAction(null, parms, (data) => {if (data) alert(data);});
			} else alert('Must be an XML file');
		}
		break;
	case 'sql3':
		if (!scnt || oneItem()) {
			parms = '?dir='+curDir;
			if (scnt) {
				parms = '?dbf='+curDir+curFref;
			}
			var fvurl = fmx_appPath+'pla/phpliteadmin.php'+parms;
			pop(fvurl,screen.availHeight,screen.availWidth*0.8);
		}
		break;
	case 'fmxi':
		const resp = (data) => {
				let DtD;
				let updt = data.updt ? data.updt.split('|') : null;
				if (updt) {
					DtD = {...aMsgDlg, ...{buttons:{'update': {text: 'Update Now'}}}};
				} else {
					DtD = aMsgDlg;
				}
				rjOpenDlg(evt, DtD, {
					fattrs:{'span.aMsg':data.msg+'<br>&nbsp;','div.titl span':'FMX - Hosted File Manager'},
					cb: (a) => {
						if (a == 'update' && confirm('It is a good idea to backup first. Do you want to continue with the update?')) {
							parms = {act: 'updt', nver: updt[1]};
							postAndRefresh(parms);
						}
					}
				});
			};
		postAction('fmxi', {}, resp, true);
		break;
	case 'cmcs':
		postAction('CLIC', {}, (data) => {
			let fpop = _rj.id('footerPop');
			let utl = rjHtmlElement('div', {id: 'util-view'}, data);
			utl.addEventListener('click', (e) => {
					document.getElementById('cmdlin').value = e.target.closest('[data-cmd]').dataset.cmd;
					fpop.removeChild(utl);
				}
			);
			utl.style.left = (evt.pageX+8) + 'px';
			fpop.appendChild(utl);
			utl.style.top = (-utl.clientHeight+8) + 'px';
		});
		break;
	case 'mnu':
		break;
	default:
		alert('?'+cmd+'?');
		break;
	}
}

var editWindow;

function doRenameFile (fpath, evt) {
	rjOpenDlg(evt, fRenDlg, {
		fattrs:{'input[name="oldnm"]|value':fpath, 'input[name="nunam"]|value':fpath},
		cb: (act, fd) => {
			if (act != 'rename') return;
			let nnm = fd.get('nunam').trim();
			if (!nnm) { alert('Please enter a valid name'); return false; }
			let parms = {
				act: 'fren',
				fref: curDir + fd.get('oldnm'),
				nunm: curDir + nnm
				};
			postAndRefresh(parms);
		}
	});
}
function doViewFile (fpath) {
	var fvurl = fmx_appPath+'filwin.php?fref='+fpath;
	pop(fvurl,675,900);
}
function doEditFile (fpath, intab) {
	let pfx = '';
	let ext = fpath.split('.').pop();
	if (['htm', 'html'].indexOf(ext) >= 0) {
		if (confirm('Use WYSIWYG html editor?')) pfx = 'h';
	}
	if (intab) {
		editWindow = window.open(fmx_appPath+pfx+'filedwin.php?t=1&fref='+fpath, '_blank');
	} else {
		editWindow = pop(fmx_appPath+pfx+'filedwin.php?fref='+fpath,screen.availHeight,screen.availWidth);
	}
}
function doEditImage (fpath) {
	popPost(fmx_appPath+'imgedtwin.php', {'fref':fpath}, 'imgedt', screen.availHeight, Math.min(1200,screen.availWidth));
}
function doManageZip (fpath) {
	let feurl = fmx_appPath+'arcview.php?fref='+fpath;
	editWindow = pop(feurl,screen.availHeight*0.75,screen.availWidth*0.5);
}

function doFileAction (act, elem, evt) {
	if (evt) { evt.preventDefault();evt.stopPropagation(); }
	let fName = elem.closest('[data-fref]').dataset.fref;
	let fileoi = encodeURIComponent(curDir+fName);
	let parms;
	switch (act) {
		case 'finf':
			parms = {act: act, fref: fileoi};
			postAction(null, parms, (data) => rjOpenDlg(evt, aMsgDlg, {
					fattrs:{'span.aMsg':data+'<br>&nbsp;','div.titl span':'File info for: '+fName}
				})
			);
			break;
		case 'fvue':
			doViewFile(fileoi);
			break;
		case 'fedt':
			doEditFile(fileoi, evt ? evt.altKey : false);
			break;
		case 'iedt':
			doEditImage(fileoi);
			break;
		default:
			alert('?'+act+'?');
			break;
	}
}

function fils2up () {
	if (document.cliterm.cmdlin.value === '') {
		alert('Please enter a command.');
		return false;
	} else {
		var cmd = document.cliterm.cmdlin.value;
		if (cmd.indexOf('$$')>0) {
			document.cliterm.cmdlin.value = cmd.replace('$$', makeFileList(sessionStorage.fmx_mrkd, false));
		}
		document.cliterm.submit();
		return true;
	}
}

function selectionAction (fedt) {
	var sel = '';
	if (window.getSelection !== 'undefined') {
		sel = window.getSelection();
	} else if (document.selection !== 'undefined') {
		if (document.selection.type == 'Text') {
			sel = document.selection.createRange().text;
		}
	}
	var fpth = curDir+sel;
	if (fedt) { doEditFile(fpth); }
	else { doViewFile(fpth); }
}

function display_cmmStorage (stor) {
	if (!stor) { alert('[ empty ]'); return; }
	var disp = '';
	var pecs = stor.split('\u0000');
	for (var x in pecs) {
		var prts = decodeURIComponent(pecs[x]).split('&');
		for (var i=0; i<prts.length; i++) {
			if (i>0) disp += '    ';
			disp += prts[i] + '\n';
		}
	}
	alert(disp);
}

function show_mcount (mid, stor) {
	let elm = document.getElementById(mid);
	let pp = elm.innerHTML.split('(');
	if (!stor) { elm.innerHTML = pp[0]; return; }
	let pecs = stor.split('\u0000');
	let ct = 0;
	for (let x in pecs) {
		let prts = decodeURIComponent(pecs[x]).split('&');
		for (let i=0; i<prts.length; i++) {
			if (i) ct++;
		}
	}
	elm.innerHTML = (pp[0]+'('+ct+')');
}

function bump_mcount (mid, num) {
	let elm = document.getElementById(mid);
	let pp = elm.innerHTML.split('(');
	if (num<0) {
		elm.innerHTML = pp[0];
	} else if (pp[1]) {
		let pp1 = pp[1];
		let pn = pp1.substr(0, pp1.length-1);
		elm.innerHTML = (pp[0]+'('+((+pn)+num)+')');
	} else {
		elm.innerHTML = (pp[0]+'('+num+')');
	}
}

// context menu actions
function cm_del (itm, fld) {
	var fle = itm.closest('[data-fref]').dataset.fref;
	var ctx = fld ? 'folder and contents' : 'file';
	if (confirm('Are you sure you want to delete this '+ctx+': '+fle+' ?')) {
	//	postAndRefresh('act=delf&dir='+encodeURIComponent(curDir)+'&files[]='+fle);
		var parms = {
			act: 'delf',
			dir: curDir,
			'files[]': fle
			};
		postAndRefresh(parms);
	}
}
function cm_dld (itm, fld) {
	var parms = {
		act: 'dnld',
		dir: curDir,
		'files[]': itm.closest('[data-fref]').dataset.fref + (fld?'/':'')
		};
	postAction(null, parms,
		(data) => {
			if (data) { downloadFile(data.fpth,data.rad=='Y',false); }
			else { alert('download not available'); }
			},
		true);
}
function cm_dup (itm) {
	var parms = {
		act: 'dupl',
		fref: curDir + itm.closest('[data-fref]').dataset.fref
		};
	postAndRefresh(parms);
}
function cm_ren (itm) {
	var curfn = itm.closest('[data-fref]').dataset.fref;
	doRenameFile(curfn, event);
}
function cm_zip (itm, fld) {
	var curfn = itm.closest('[data-fref]').dataset.fref;
	var trmFrm = document.forms.cliterm;
	var zcmd = 'zip ';
	if (fld) { zcmd += '-r '; }
	var destfn = curfn.replace(/\s/g,'_');
	trmFrm.cmdlin.value = zcmd+destfn+'.zip "'+curfn+'"';
	trmFrm.submit();
}
function cm_slnk () {
	if (!sessionStorage.fmx_mrkd) {
		alert('Mark something to link first');
		return;
	}
	var itms = sessionStorage.fmx_mrkd.split('\u0000');
	if (itms.length != 1) {
		alert('Can SymLink to only one location at a time');
		return;
	}
	var fpts = itms[0].split('&');
	var prts = fpts[0].split('=');
	var dir = prts[1].replace(/%2F/g,'/');
	prts = fpts[1].split('=');
	var fil = prts[1].replace(/%2F/g,'/');
	var lnkn = prompt('Link to marked:', '');
	if (lnkn===null) return;
	var parms = {
		act: 'slnk',
		fref: dir+'/'+fil,
		tref: curDir,
		alnk: lnkn
		};
	postAndRefresh(parms);
}

const ctxmenus = {
	filCtx: {
		1: {text: 'Edit', act: (s) => doFileAction('fedt', s, null)},
		2: {text: 'Delete', act: (s) => cm_del(s,false)},
		3: {text: 'Download', act: (s) => cm_dld(s,false)},
		4: {text: 'Duplicate', act: (s) => cm_dup(s,false)},
		5: {text: 'Rename', act: (s) => cm_ren(s)},
		6: {text: 'Zip', act: (s) => cm_zip(s,false)}
	},
	fldCtx: {
		2: {text: 'Delete', act: (s) => cm_del(s,true)},
		3: {text: 'Download', act: (s) => cm_dld(s,true)},
		4: {text: 'Duplicate', act: (s) => cm_dup(s,true)},
		5: {text: 'Rename', act: (s) => cm_ren(s)},
		6: {text: 'Zip', act: (s) => cm_zip(s,true)}
	},
	uplCtx: {
		1: {text: 'H5win', act: () => upldAction.H5w()},
		2: {text: 'H5ovr', act: () => upldAction.H5o()},
		3: {text: 'L4win', act: () => upldAction.L4w()},
		4: {text: 'L4ovr', act: () => upldAction.L4o()}
	},
	cppCtx: {
		1: {text: 'Clear', act: () => {sessionStorage.removeItem('fmx_cppa'); bump_mcount('cppaMenu', -1); }},
		2: {text: 'Display', act: () => display_cmmStorage(sessionStorage.fmx_cppa)}
	},
	delCtx: {
		1: {text: 'Truly Delete', act: () => doMenuAction('delf', null)},
		2: {text: 'Empty Trash', act: () => doMenuAction('mpty', null)},
		3: {text: 'View Trash', act: () => doMenuAction('tvew', null)}
	},
	mrkCtx: {
		1: {text: 'Clear', act: () => {sessionStorage.removeItem('fmx_mrkd'); bump_mcount('markMenu', -1); }},
		2: {text: 'Display', act: () => display_cmmStorage(sessionStorage.fmx_mrkd)},
		3: {text: 'SymLink', act: () => cm_slnk()}
	},
	mvtCtx: {
		1: {text: 'Clear', act: () => {sessionStorage.removeItem('fmx_mvto'); bump_mcount('mvtoMenu', -1); }},
		2: {text: 'Display', act: () => display_cmmStorage(sessionStorage.fmx_mvto)}
	}
};

// initialize session settings and the UI
const fmx_init = () => {
	// some functionality checks
	try { sessionStorage.fmx_ok = 1; }
	catch(err) { alert('Your browser \'sessionStorage\' is not functioning. (private browsing?) Not all functions of FMX will work successfully.'); }

	sessionStorage.fmx_curD = curDir;

	_rj.ae('fmnu', 'click', (e)=>{e.preventDefault(); doMenuAction(e.target.closest('[data-mnu]').dataset.mnu,e); });
	_rj.ae('footerPopContent', 'click', (e)=>{e.preventDefault(); doMenuAction(e.target.closest('[data-mnu]').dataset.mnu,e); });
	_rj.ae('ftbl', 'click', (e)=>{
		let act = e.target.closest('[data-act]')?.dataset.act;
		if (act) {
			e.preventDefault();
			doFileAction(act, e.target, e);
		}
	});

//	document.querySelectorAll('nav>ul>li ul').forEach(elm =>{elm.style.display='none';elm.classList.remove('fallback')});
//	_rj.ae(_rj.qs('nav li>ul',true), 'mouseover', (e) => console.log(e)/*e.target.firstElementChild.style.display='block'*/);
	document.querySelectorAll('nav>ul>.drpm').forEach(elm =>{elm.addEventListener('click',(e)=>{
			if (e.target.parentElement.className == 'drpm') e.stopPropagation();
			e.target.closest('.drpm').style.display='block';
			let li = e.target.parentElement;
			let ddm = li.querySelector('ul');
			if (ddm) {
				ddm.style.display='block';
				fmx_ui.ddm = ddm;
			}
		});
	});
	document.querySelectorAll('nnav .drpm ul').forEach(elm =>{elm.addEventListener('mouseout',(e)=>{
		//	let li = e.target.parentElement;
		//	li.querySelector('ul').style.display='nonee';
			let li = e.target;
			if (li.nodeName=='UL') li.style.display='nonee';
		},true);
	});

	// checkbox/checkall interaction
	let chkboxes = document.querySelectorAll('.fsel');
	let lastChecked = null;

	chkboxes.forEach(elm => {elm.addEventListener('click',function (e) {
		// shift click to extend selections
		if (e.shiftKey && lastChecked) {
			let start = Array.from(chkboxes).indexOf(lastChecked);
			let end = Array.from(chkboxes).indexOf(elm);
			// Ensure start is less than end
			if (start > end) {
				[start, end] = [end, start];
			}
			// Select all checkboxes in the range
			for (let i = start; i <= end; i++) {
				chkboxes[i].checked = this.checked;
			}
		}
		lastChecked = this;

		// manage check-all state
		if (this.checked) {
			let isAllChecked = 0;
			chkboxes.forEach((cbx) => {if (!cbx.checked) isAllChecked = 1;});
			if (isAllChecked === 0) _rj.id('checkAll').checked = true;
		} else {
			_rj.id('checkAll').checked = false;
		}
	})});



	// attach menu counts
	show_mcount('cppaMenu', sessionStorage.fmx_cppa);
	show_mcount('markMenu', sessionStorage.fmx_mrkd);
	show_mcount('mvtoMenu', sessionStorage.fmx_mvto);

	// let's try file drag-n-drop
	// now defunct, I guess
//	window.filesDrop = new FileDropper(document.getElementById('filsform'), (fils) => upldAction.H5o(()=>fupQadd2(fils)) );
};

// start things up after DOM loaded
document.addEventListener('DOMContentLoaded', () => fmx_init());

/*
// some reusable classes
class FileDropper {
	constructor(elm, cb) {
		elm.addEventListener('dragover', this, false);
		elm.addEventListener('drop', this, false);
		elm.addEventListener('dragenter', this, false);
		elm.addEventListener('dragleave', this, false);
		this.cb = cb;
	}
	handleEvent(e) {
		e.preventDefault();
		switch (e.type) {
//			case 'dragover':
//				e.preventDefault();
//				break;
			case 'drop':
		//		e.preventDefault();
				let fils = e.dataTransfer.files;
				this.cb(fils);
				break;
			case 'dragenter':
				e.target.classList.add('upld-body');
				break;
			case 'dragleave':
				e.target.classList.remove('upld-body');
				break;
		}
	}
}
*/
