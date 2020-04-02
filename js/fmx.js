function refreshFilst () {
	window.location = window.location.href.split("#")[0];
}

function refreshFilstO (so) {
	var wlp = window.location.href.split("#")[0].split("?");
	if (wlp[1]) {
		window.location = wlp[0] + "?" + wlp[1].split("&")[0] + "&O=" + so;
	} else {
		window.location = wlp[0] + "?O=" + so;
	}
}

function postAndRefresh (parms) {
	$.post(fmx_AJ, parms, function(data,textStatus,jqXHR) {
			if (data) { alert(data); }
			else { refreshFilst(); }
		}
	);
}

var aMsgDlg = {
	cselect: '#aMsgDlog',
	buttons: {
		'Okay`prm': function() {
			myCloseDlg(this);
			}/*,
		Cancel: function() {
			myCloseDlg(this);
			}*/
		}
	};

var aSchDlg = {
	cselect: '#aSchDlog',
	buttons: {
		'Search`prm': function() {
			var frm = document.myUIform;
			if (!frm.sterm.value.trim()) { alert("Please enter a valid search term"); return; }
			var trm = frm.sterm.value.trim(),
				cmd = frm.cmd.value,
				trmFrm = document.forms.cliterm;
			if (cmd==='srhf') {
				sessionStorage.fmx_strmf = trm;
				trmFrm.cmdlin.value = 'find ./ -name '+trm+' -ls';
			} else {
				sessionStorage.fmx_strmc = trm;
				trmFrm.cmdlin.value = 'grep -R -I '+trm+' *';
			}
			trmFrm.submit();
			myCloseDlg(this);
			}
		}
	};

var fRenDlg = {
	cselect: '#fRenDlog',
	modal: true,
	buttons: {
		'Rename`prm': function() {
			var frm = document.myUIform;
			if (!frm.nunam.value.trim()) { alert("Please enter a valid name"); return; }
			var parms = {
				act: 'fren',
				fref: curDir + frm.oldnm.value,
				nunm: curDir + frm.nunam.value.trim()
				};
			myCloseDlg(this);
			postAndRefresh(parms);
			}
		}
	};

var fNamDlg = {
	cselect: '#fNamDlog',
	buttons: {
		'Create`prm': function() {
			var frm = document.myUIform;
			if (!frm.fref.value.trim()) { alert("Please enter a valid name"); return; }
			var parms = {
				act: frm.act.value,
				fref: curDir + frm.fref.value.trim()
				};
			myCloseDlg(this);
			postAndRefresh(parms);
			}
		}
	};

// file upload methods
var upldAction = {
	// HTML5 w/progress in a popup window
	H5w: function(){ popUp(fmx_appPath+'filupld5d.php'); },
	// HTML5 w/progress in a div overlay
	H5o: function(cbf){ $('#upload').jqm({ajax:fmx_appPath+'filupld5dm.php', ajaxText:'Loading...', onLoad: cbf, onHide: function(h){refreshFilst();}, target:'.upldr', overlay:5}).jqmShow(); },
	// legacy HTML in a popup window
	L4w: function(){ popUp(fmx_appPath+'filupld.php'); },
	// legacy HTML in a div overlay
	L4o: function(){ $('#upload').jqm({ajax:fmx_appPath+'filupldm.php', ajaxText:'Loading...', onHide: function(h){refreshFilst();}, target:'.upldr',overlay:5}).jqmShow(); },
	// chunked upload for very large files
	Chk: function(){ popUp(fmx_appPath+'upchunk.php'); }
	};

function downloadFile (A, cdl, asf) {
	var dlURL = fmx_appPath+'fildnld.php?fle=' + encodeURI(A) + (cdl ? '&tcdl=1&rad=Y' : '') + (asf ? ('&asf='+asf) : ''); //alert(dlURL);
	var dlframe = document.createElement("iframe");
	// set source to desired file
	dlframe.src = dlURL;
	// This makes the IFRAME invisible to the user.
	dlframe.style.display = "none";
	// Add the IFRAME to the page.  This will trigger the download
	document.body.appendChild(dlframe);
}

function makeFileList (fstr, htm) {
	var sep = htm ? '<br />' : ' ';
	var rslt = '';
	var itm, itms = fstr.split("\u0000");
	var fpts, fdr, fpt, fpa, cdl = encodeURIComponent(curDir).length;
	for (itm in itms) {
		fpts = itms[itm].split("&");
		fdr = '';
		for (fpt in fpts) {
			fpa = fpts[fpt].split("=");
			if (fpt*1===0) { fdr = fpa[1].substr(cdl); }
			else { rslt += fdr + '%2F' + fpa[1] + sep; }
		}
	//console.log(cdl);console.log(fdr);console.log(curDir);
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
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon="toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width="+w1+",height="+h1+",left="+w2+",top="+h2;
	return window.open(url, "", wcon);
}

function popPost (url, data, name, h1, w1) {
	var flds = "", key;
	for (key in data) { flds += '<input type="hidden" name="'+key+'" value="'+data[key]+'" />'; }
	var ppf = $('<form action="'+url+'" method="post" target="'+name+'">'+flds+'</form>');
	$(document.body).append(ppf);
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon="toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width="+w1+",height="+h1+",left="+w2+",top="+h2;
	window.open("", name, wcon).focus();
	ppf[0].submit();
}

function popUp (url) {
	pop(url,240,416);
}

function doMenuAction (cmd,evt) {
	var slctd = $(".fsel:checked"),
		scnt = slctd.length,
		parms, curfn, trmFrm, destfn,
		oneItem = function () { if (!scnt) { alert('An item needs to be selected'); } else if (scnt>1) { alert('Please select only one item.'); } else { return true; } return false; },
		hasSome = function () { if (scnt) { return true; } alert('Some items need to be selected'); return false; };
	switch (cmd) {
	case 'cppa':
		if (scnt) {
			sessionStorage.fmx_cppa = $("form[name='filst']").serialize();
			show_mcount('#cppaMenu', sessionStorage.fmx_cppa);
		} else if (sessionStorage.fmx_cppa) {
			postAndRefresh('act=cppa&todr='+encodeURIComponent(curDir)+'&'+sessionStorage.fmx_cppa);
		}
		break;
	case 'trsh':
		if (hasSome() && ((scnt==1) || confirm('You have multiple files selected. Are you sure you want to trash ALL the selected files?'))) {
			postAndRefresh('act=trsh&'+$("form[name='filst']").serialize());
		}
		break;
	case 'mpty':
		postAndRefresh('act=mpty');
		break;
	case 'delf':
		if (hasSome() && ((scnt==1) || confirm('You have multiple files selected. Are you sure you want to delete ALL the selected files?'))) {
			postAndRefresh('act=delf&'+$("form[name='filst']").serialize());
		}
		break;
	case 'dnld':
		if (hasSome()) {
			parms = 'act=dnld&'+$("form[name='filst']").serialize();
			$('div.dnldprg').css('display','inline');
			$.post(fmx_AJ, parms, function(data,textStatus,jqXHR) {
				$('div.dnldprg').css('display','none');
				if (data) { downloadFile(data.fpth,data.rad=='Y',false); }
				else { alert('download not available'); }
			},'json');
		}
		break;
	case 'dupl':
		if (oneItem()) {
			parms = {
				act: 'dupl',
				fref: curDir + $(slctd[0]).parents('tr').attr('data-fref')
				};
			postAndRefresh(parms);
		}
		break;
	case 'mark':
		parms = (sessionStorage.fmx_mrkd === undefined) ? '' : (sessionStorage.fmx_mrkd+"\u0000");
		if (scnt) {
			sessionStorage.fmx_mrkd = parms + $("form[name='filst']").serialize();
			bump_mcount('#markMenu', scnt);
		}
		break;
	case 'mmiz':
		if (hasSome()) {
			postAndRefresh('act=mmiz&'+$("form[name='filst']").serialize());
		}
		break;
	case 'mvto':
		if (scnt) {
			sessionStorage.fmx_mvto = $("form[name='filst']").serialize();
			show_mcount('#mvtoMenu', sessionStorage.fmx_mvto);
		} else {
			if (!sessionStorage.fmx_mvto) {
				alert('Nothing previously selected to move');
				break;
			}
			parms = sessionStorage.fmx_mvto;
			sessionStorage.removeItem("fmx_mvto");
			postAndRefresh('act=mvto&todr='+encodeURIComponent(curDir)+'&'+parms);
		}
		break;
	case 'nfle':
	case 'nfld':
		myOpenDlg(evt,fNamDlg,{'act':cmd}, cmd=='nfle'?'New File':'New Folder');
		break;
	case 'refr':
		refreshFilst();
		break;
	case 'rnam':
		if (oneItem())  {
			curfn = $(slctd[0]).parents('tr').attr('data-fref');
			myOpenDlg(evt,fRenDlg,{'old':curfn,'new':curfn});
		}
		break;
	case 'srhf':
	case 'srhc':
		var prmt = 'file';
		var strm = sessionStorage.fmx_strmf;
		if (cmd=='srhc') {
			prmt = 'content';
			strm = sessionStorage.fmx_strmc;
		}
		myOpenDlg(evt,aSchDlg,{'cmd':cmd,'trm':strm});
		break;
	case 'turl':
		// curl a file to a URL
		if (oneItem()) {
			$('#upload').jqm({ajax:fmx_appPath+'filcurlm.php?t=1', ajaxText:'Loading...', target:'.upldr',overlay:5}).jqmShow();
		}
		break;
	case 'furl':
		// curl a file from a URL
		$('#upload').jqm({ajax:fmx_appPath+'filcurlm.php', ajaxText:'Loading...', target:'.upldr',overlay:5}).jqmShow();
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
			curfn = /*curDir+*/$(slctd[0]).parents('tr').attr('data-fref');
			trmFrm = document.forms.cliterm;
			if (cmd=='zip') {
				var zcmd = "zip ";
				if ($(slctd[0]).parent().next().hasClass('foldCtxt')) { zcmd += "-r "; }
				destfn = curfn.replace(/\s/g,'_');
				trmFrm.cmdlin.value = zcmd+destfn+'.zip "'+curfn+'"';
				if (evt.shiftKey) {
					var xyz = prompt('COMMAND:',trmFrm.cmdlin.value+' -x "*/sv_*" -x "*/.git*"');
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
			curfn = /*curDir+*/$(slctd[0]).parents('tr').attr('data-fref');
			var fileoi = encodeURIComponent(curDir+curfn);
			doManageZip(fileoi);
		}
		break;
	case 'webv':
		if (oneItem()) {
			curfn = $(slctd[0]).parents('tr').attr('data-fref');
			if (evt.shiftKey) {
				var xyz = prompt('URL:',curfn);
				if (xyz) { curfn = xyz; }
				else break;
			}
			var wPath = ctxPrf+curDir.slice(curDir.search("/"));
			pop(wPath+curfn,screen.availHeight,1200);
		}
		break;
	case 'gitr':
		if (oneItem()) {
			if (".git" == $(slctd[0]).parents('tr').attr('data-fref')) {
				parms = '?dir='+curDir;
				pop(fmx_appPath+'gitter.php'+parms,screen.availHeight,1200);
			} else {
				alert("Please select a '.git' folder")
			}
		}
		break;
	case 'jxtr':
		if (oneItem()) {
			curfn = curDir+$(slctd[0]).parents('tr').attr('data-fref');
			var m = curfn.match(/([^\/\\]+)\.(\w+)$/);
			if (m && m[2] == 'xml') {
				parms = {
					act: 'jxtr',
					fref: curfn,
					dir: curDir
					};
				$.post(fmx_AJ, parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data); }
				});
			} else alert('Must be an XML file');
		}
		break;
	case 'sql3':
		if (!scnt || oneItem()) {
			parms = '?dir='+curDir;
			if (scnt) {
				parms = '?dbf='+curDir+$(slctd[0]).parents('tr').attr('data-fref');
			}
			var fvurl = fmx_appPath+'pla/phpliteadmin.php'+parms;
			pop(fvurl,screen.availHeight,screen.availWidth*0.8);
		}
		break;
	case 'pvck':
		parms = '?dir='+curDir;
		pop(fmx_appPath+'phpverreq.php'+parms,screen.availHeight,1200);
		break;
	case 'fmxi':
		parms = {act: 'fmxi'};
		$.post(fmx_AJ, parms, function(data,textStatus,jqXHR) {
				if (data) {
					var DtD;
					if (data.updt) {
						var updt = data.updt.split('|');
						DtD = $.extend(true, {}, aMsgDlg, {buttons:{'Update now':function(){if (confirm('It is a good idea to backup first. Do you want to continue with the update?')) {
							parms = {act: 'updt', nver: updt[1]};
							$.post(fmx_AJ, parms, function(data,textStatus,jqXHR) {
								if (data) { alert(data); }
								else refreshFilst();
							});
							myCloseDlg(this);
						}}}});
					} else {
						DtD = aMsgDlg;
					}
					myOpenDlg(evt,DtD,{'msg':data.msg},'FMX - Hosted File Manager');
				}
			},'json');
		break;
	case 'cmcs':
		var utilview = document.createElement('div');
		utilview.id = 'util-view';
		var utilLabel = document.createElement('div');
		utilLabel.innerHTML = 'Loading ...';
		utilview.style.left = (evt.clientX-60) + 'px';
		utilview.style.top = (evt.clientY-48) + 'px';
		utilview.appendChild(utilLabel);
		document.body.appendChild(utilview);

		parms = {act: 'CLIC'};
		$(utilview).load(fmx_AJ, parms, function(data,textStatus,jqXHR) {
				utilview.style.top = (evt.clientY-utilview.clientHeight-20) + 'px';
				$("#util-view div").click(function(e) {e.preventDefault(); doFillCLI($(this).attr('data-cmd')); document.body.removeChild(utilview); });
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

function doViewFile (fpath) {
	var fvurl = fmx_appPath+'filwin.php?fref='+fpath;
	pop(fvurl,675,900);
}
function doEditFile (fpath) {
	var feurl = fmx_appPath+'filedtwin.php?fref='+fpath;
	editWindow = pop(feurl,screen.availHeight,screen.availWidth);
}
function doEditImage (fpath) {
	popPost(fmx_appPath+"imgedtwin.php", {"fref":fpath}, "imgedt", screen.availHeight, Math.min(1200,screen.availWidth));
}
function doManageZip (fpath) {
	var feurl = fmx_appPath+'zipmngr.php?fref='+fpath;
	editWindow = pop(feurl,screen.availHeight*0.75,screen.availWidth*0.5);
}

function doFileAction (act,elem,evt) {
	if (evt) { evt.preventDefault();evt.stopPropagation(); }
	var fName = $(elem).parents('tr').attr('data-fref');
	var fileoi = encodeURIComponent(curDir+fName);
	var parms;
	switch (act) {
		case 'finf':
			parms = {
				act: act,
				fref: fileoi
				};
			$.post(fmx_AJ, parms, function(data,textStatus,jqXHR) {
					if (data) {
						myOpenDlg(evt,aMsgDlg,{'msg':data},'File info for: '+fName);
					}
				});
			break;
		case 'fvue':
			doViewFile(fileoi);
			break;
		case 'fedt':
			doEditFile(fileoi);
			break;
		case 'iedt':
			doEditImage(fileoi);
			break;
		default:
			alert('?'+act+'?');
			break;
	}
}

function doFillCLI (cmd) {
	$('#cmdlin').val(cmd);
}

function allSelect (evt, elem) {
	if (elem.checked) {
		$('.fsel').prop('checked',true);
	} else {
		$('.fsel').prop('checked',false);
	}
}

function fils2up () {
	if (document.cliterm.cmdlin.value === "") {
		alert("Please enter a command.");
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
	var sel = "";
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
	if (!stor) { alert("[ empty ]"); return; }
	var disp = '';
	var pecs = stor.split("\u0000");
	for (var x in pecs) {
		var prts = decodeURIComponent(pecs[x]).split('&');
		for (var i=0; i<prts.length; i++) {
			if (i>0) disp += '    ';
			disp += prts[i] + "\n";
		}
	}
	alert(disp);
}

function show_mcount (mid, stor) {
	var mm = $(mid).html();
	var pp = mm.split('(');
	if (!stor) { $(mid).html(pp[0]); return; }
	var pecs = stor.split("\u0000");
	var ct = 0;
	for (var x in pecs) {
		var prts = decodeURIComponent(pecs[x]).split('&');
		for (var i=0; i<prts.length; i++) {
			if (i) ct++;
		}
	}
	$(mid).html(pp[0]+'('+ct+')');
}

function bump_mcount (mid, num) {
	var mm = $(mid).html();
	var pp = mm.split('(');
	if (num<0) {
		$(mid).html(pp[0]);
	} else if (pp[1]) {
		var pp1 = pp[1];
		var pn = pp1.substr(0, pp1.length-1);
		$(mid).html(pp[0]+'('+((+pn)+num)+')');
	} else {
		$(mid).html(mm+'('+num+')');
	}
}

// context menu actions
function cm_del (itm, fld) {
	var fle = $(itm).parents('tr').attr('data-fref');
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
		'files[]': $(itm).parents('tr').attr('data-fref') + (fld?'/':'')
		};
	$.post(fmx_AJ, parms, function(data,textStatus,jqXHR) {
		if (data) { downloadFile(data.fpth,data.rad=='Y',false); }
		else { alert('download not available'); }
	},'json');
}
function cm_dup (itm, fld) {
	var parms = {
		act: 'dupl',
		fref: curDir + $(itm).parents('tr').attr('data-fref')
		};
	postAndRefresh(parms);
}
function cm_ren (itm) {
	var curfn = $(itm).parents('tr').attr('data-fref');
	myOpenDlg(null,fRenDlg,{'old':curfn,'new':curfn});
}
function cm_zip (itm, fld) {
	var curfn = $(itm).parents('tr').attr('data-fref');
	var trmFrm = document.forms.cliterm;
	var zcmd = "zip ";
	if (fld) { zcmd += "-r "; }
	var destfn = curfn.replace(/\s/g,'_');
	trmFrm.cmdlin.value = zcmd+destfn+'.zip "'+curfn+'"';
	trmFrm.submit();
}
function cm_slnk () {
	if (!sessionStorage.fmx_mrkd) {
		alert("Mark something to link first");
		return;
	}
	var itms = sessionStorage.fmx_mrkd.split("\u0000");
	if (itms.length != 1) {
		alert("Can SymLink to only one location at a time");
		return;
	}
	var fpts = itms[0].split("&");
	var prts = fpts[0].split("=");
	var dir = prts[1].replace(/%2F/g,'/');
	prts = fpts[1].split("=");
	var fil = prts[1].replace(/%2F/g,'/');
	var lnkn = prompt("Link to marked:", "");
	if (lnkn===null) return;
	var parms = {
		act: 'slnk',
		fref: dir+'/'+fil,
		tref: curDir,
		alnk: lnkn
		};
	postAndRefresh(parms);
}

// initialize session settings and the UI
$(function() {
	// some functionality checks
	try { sessionStorage.fmx_ok = 1; }
	catch(err) { alert("Your browser 'sessionStorage' is not functioning. (private browsing?) Not all functions of FMX will work successfully."); }

	sessionStorage.fmx_curD = curDir;

	$("#fmnu [data-mnu]").click(function(e) {e.preventDefault(); doMenuAction($(this).attr('data-mnu'),e); });
	$("#footerPopContent [data-mnu]").click(function(e) {e.preventDefault(); doMenuAction($(this).attr('data-mnu'),e); });
	$("#ftbl [data-act]").click(function(e) {e.preventDefault(); doFileAction($(this).attr('data-act'),this,e); });
	$('nav li ul').hide().removeClass('fallback');
	$('nav li').hover(function () {
		$('ul', this).stop(true,true).fadeToggle(100);
	});

	// checkbox/checkall interaction
	$(".fsel").click(function () {
		if (this.checked) {
			var isAllChecked = 0;
			$(".fsel").each(function() {
				if (!this.checked)
					isAllChecked = 1;
			});
			if (isAllChecked === 0) {
				$("#checkAll").prop("checked", true);
			}
		} else {
			$("#checkAll").prop("checked", false);
		}
	});

	// attach menu counts
	show_mcount('#cppaMenu', sessionStorage.fmx_cppa);
	show_mcount('#markMenu', sessionStorage.fmx_mrkd);
	show_mcount('#mvtoMenu', sessionStorage.fmx_mvto);

	// setup contextual menus
	$('a.cppaMenu').contextMenu('clrdMenu', {
		bindings: {
			'clrdClr': function(t) { sessionStorage.removeItem("fmx_cppa"); bump_mcount('#cppaMenu', -1); },
			'clrdDsp': function(t) { display_cmmStorage(sessionStorage.fmx_cppa); }
		}
	});
	$('a.delfMenu').contextMenu('delfMenu', {
		bindings: {
			'delfTrue': function(t) { doMenuAction('delf', null); },
			'delfMpty': function(t) { doMenuAction('mpty', null); }
		}
	});
	$('a.markMenu').contextMenu('clrdMenuSL', {
		bindings: {
			'clrdClr': function(t) { sessionStorage.removeItem("fmx_mrkd"); bump_mcount('#markMenu', -1); },
			'clrdDsp': function(t) { display_cmmStorage(sessionStorage.fmx_mrkd); },
			'symLnk': function(t) { cm_slnk(); }
		}
	});
	$('a.mvtoMenu').contextMenu('clrdMenu', {
		bindings: {
			'clrdClr': function(t) { sessionStorage.removeItem("fmx_mvto"); bump_mcount('#mvtoMenu', -1); },
			'clrdDsp': function(t) { display_cmmStorage(sessionStorage.fmx_mvto); }
		}
	});
	$('a.upldMenu').contextMenu('upldMenu', {
		onContextMenu: function(e) { /*sessionStorage.fmx_curD = curDir;*/ return true; },
		bindings: {
			'H5w': function(t) { upldAction.H5w(); },
			'H5o': function(t) { upldAction.H5o(); },
			'L4w': function(t) { upldAction.L4w(); },
			'L4o': function(t) { upldAction.L4o(); },
			'Chk': function(t) { upldAction.Chk(); }
		}
	});
	$('td.fileCtxt').contextMenu('fileCtxt', {
		bindings: {
			'cfi_edt': function(t) { doFileAction('fedt', t, null); },
			'cfi_del': function(t) { cm_del(t,false); },
			'cfi_dld': function(t) { cm_dld(t,false); },
			'cfi_dup': function(t) { cm_dup(t,false); },
			'cfi_ren': function(t) { cm_ren(t); },
			'cfi_zip': function(t) { cm_zip(t,false); }
		}
	});
	$('td.foldCtxt').contextMenu('foldCtxt', {
		bindings: {
			'cfo_del': function(t) { cm_del(t,true); },
			'cfo_dld': function(t) { cm_dld(t,true); },
			'cfo_dup': function(t) { cm_dup(t,true); },
			'cfo_ren': function(t) { cm_ren(t); },
			'cfo_zip': function(t) { cm_zip(t,true); }
		}
	});

	// let's try file drag-n-drop
	var $form = $('#filsform');
	$form.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) { e.preventDefault(); e.stopPropagation(); })
	.on('dragover dragenter', function() { $form.addClass('upld-body'); })
	.on('dragleave dragend drop', function() { $form.removeClass('upld-body'); })
	.on('drop', function(e) { var fils = e.originalEvent.dataTransfer.files; upldAction.H5o(function(){ fupQadd2(fils); }); });
});
