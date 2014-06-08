function refreshFilst() {
	window.location.reload();
}

function postAndRefresh(parms) {
	$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
			if (data) { alert(data); }
			else { refreshFilst(); }
			});
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

var fCpmDlg = {
	cselect: '#fCpmDlog',
	buttons: {
		'{CM}': function() {
			var frm = document.myUIform;
			if (!frm.cpm2nam.value.trim()) { alert("Please enter a valid path"); return; }
			var parms = {
				act: frm.act.value,
				fref: frm.cpmfnm.value,
				tonm: frm.cpm2nam.value.trim()
				};
			myCloseDlg(this);
			postAndRefresh(parms);
			}
		}
	};

// file upload methods
var upldAction = {
	// HTML5 w/progress in a popup window
	H5w: function(){ popUp('filupld5d.php'); },
	// HTML5 w/progress in a div overlay
	H5o: function(){ $('#upload').jqm({ajax:'filupld5dm.php', ajaxText:'Loading...', target:'.upldr',overlay:5}).jqmShow(); },
	// legacy HTML in a popup window
	L4w: function(){ popUp('filupld.php'); },
	// legact HTML in a div overlay
	L4o: function(){ $('#upload').jqm({ajax:'filupldm.php', ajaxText:'Loading...', target:'.upldr',overlay:5}).jqmShow(); }
	};

function downloadFile(A, cdl, asf) {
	var dlURL = 'fildnld.php?fle=' + escape(A) + (cdl ? '&tcdl=1&rad=Y' : '') + (asf ? ('&asf='+asf) : ''); //alert(dlURL);
	var dlframe = document.createElement("iframe");
	// set source to desired file
	dlframe.src = dlURL;
	// This makes the IFRAME invisible to the user.
	dlframe.style.display = "none";
	// Add the IFRAME to the page.  This will trigger the download
	document.body.appendChild(dlframe);
}

function makeFileList(fstr, htm) {
	var sep = htm ? '<br />' : ' ';
	var rslt = '';
	var itm, itms = fstr.split("\u0000");
	var fpts, fdr, fpt, fpa;
	for (itm in itms) {
		fpts = itms[itm].split("&");
		fdr = '';
		for (fpt in fpts) {
			fpa = fpts[fpt].split("=");
			if (fpt==0) { fdr = '/'+fpa[1]; }
			else { rslt += fdr + '%2F' + fpa[1] + sep; }
		}
	}
	return rslt.replace(/%2F/g,'/');
}

function doesSupportAjaxUploadWithProgress() {

	function supportFileAPI() {
		var fi = document.createElement('INPUT');
		fi.type = 'file';
		return fi.hasOwnProperty('files');
	}

	function supportAjaxUploadProgressEvents() {
		var xhr = new XMLHttpRequest();
		return !! (xhr && (xhr.hasOwnProperty('upload')) && (xhr.upload.hasOwnProperty('onprogress')));
	}

	//return false;
	return supportFileAPI() && supportAjaxUploadProgressEvents();
}

function pop(url, h1, w1) {
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon="toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width="+w1+",height="+h1+",left="+w2+",top="+h2;
	return window.open(url, "", wcon);
}

function popPost(url, data, name, h1, w1) {
	var flds = "", key;
	for (key in data) { flds += '<input type="hidden" name="'+key+'" value="'+data[key]+'" />'; }
	var ppf = $('<form action="'+url+'" method="post" target="'+name+'">'+flds+'</form>');
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon="toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width="+w1+",height="+h1+",left="+w2+",top="+h2;
	window.open("", name, wcon).focus();
	ppf[0].submit();
}

function popUp(url) {
	pop(url,240,416);
}

function doMenuAction(cmd,evt) {
	var slctd = $(".fsel:checked"),
		scnt = slctd.length,
		parms, curfn, trmFrm, destfn,
		oneItem = function () { if (!scnt) { alert('An item needs to be selected'); } else if (scnt>1) { alert('Please select only one item.'); } else { return true; } return false; },
		hasSome = function () { if (scnt) { return true; } alert('Some items need to be selected'); return false; };
	switch (cmd) {
	case 'copy':
		if (oneItem()) {
			curfn = curDir+$(slctd[0]).parents('tr').attr('data-fref');
			myOpenDlg(evt,$.extend({}, fCpmDlg, {bover:{'{CM}':'Copy`prm'}}),{'act':cmd,'cpm':curfn},'Copy:');
		}
		break;
	case 'cppa':
		if (scnt) {
			sessionStorage.fmx_cppa = $("form[name='filst']").serialize();
		} else if (sessionStorage.fmx_cppa) {
			parms = 'act=cppa&todr='+encodeURIComponent(curDir)+'&'+sessionStorage.fmx_cppa;
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
				if (data) { alert(data); }
				else { refreshFilst(); }
			});
		}
		break;
	case 'delf':
		if (hasSome() && ((scnt==1) || confirm('You have multiple files selected. Are you sure you want to delete ALL the selected files?'))) {
			parms = 'act=delf&'+$("form[name='filst']").serialize();
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
				if (data) { alert(data); }
				else { refreshFilst(); }
			});
		}
		break;
	case 'dnld':
		if (hasSome()) {
			parms = 'act=dnld&'+$("form[name='filst']").serialize();
			$('div.dnldprg').css('display','inline');
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
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
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
				if (data) { alert(data); }
				else { refreshFilst(); }
			});
		}
		break;
	case 'mark':
		parms = sessionStorage.fmx_mrkd ? (sessionStorage.fmx_mrkd+"\u0000") : '';
		if (scnt) {
			sessionStorage.fmx_mrkd = parms + $("form[name='filst']").serialize();
		} else {
			$('#fMrk').html(sessionStorage.fmx_mrkd ? makeFileList(sessionStorage.fmx_mrkd, true) : '&lt;empty&gt;');
			$('#fMrkDlg').dialog('open');
		}
		break;
	case 'move':
		if (oneItem()) {
			curfn = curDir+$(slctd[0]).parents('tr').attr('data-fref');
			myOpenDlg(evt,$.extend({}, fCpmDlg, {bover:{'{CM}':'Move`prm'}}),{'act':cmd,'cpm':curfn},'Move:');
		}
		break;
	case 'mvto':
		if (scnt) {
			sessionStorage.fmx_mvto = $("form[name='filst']").serialize();
		} else {
			if (!sessionStorage.fmx_mvto) {
				alert('Nothing previously selected to move');
				break;
			}
			parms = 'act=mvto&todr='+encodeURIComponent(curDir)+'&'+sessionStorage.fmx_mvto;
			//alert(parms);
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
				if (data) { alert(data); }
				else { refreshFilst(); }
			});
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
	case 'upld':
		sessionStorage.fmx_curD = curDir;
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
				destfn = curfn.replace(/\s/g,'_');
				trmFrm.cmdlin.value = 'zip -r '+destfn+' "'+curfn+'"';
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
	case 'webv':
		if (oneItem()) {
			curfn = $(slctd[0]).parents('tr').attr('data-fref');
			if (evt.shiftKey) {
				var xyz = prompt('URL:',curfn);
				if (xyz) { curfn = xyz; }
				else break;
			}
			var wPath = curDir.slice(curDir.search("/"));
			pop(wPath+curfn,screen.availHeight,1200);
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
				$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data) }
				});
			} else alert('Must be an XML file');
		}
		break;
	case 'fmxi':
		parms = {act: 'fmxi'};
		$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
				if (data) {
					var DtD;
					if (data.updt) {
						var updt = data.updt.split('|');
						DtD = $.extend(true, {}, aMsgDlg, {buttons:{'Update now':function(){if (confirm('It is a good idea to backup first. Do you want to continue with the update?')) {
							parms = {act: 'updt', nver: updt[1]};
							$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
								if (data) { alert(data) }
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
		$(utilview).load("fmxjx.php", parms, function(data,textStatus,jqXHR) {
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

function doViewFile(fpath) {
	var fvurl = 'filwin.php?fref='+fpath;
	pop(fvurl,600,800);
}
function doEditFile(fpath) {
	var feurl = 'filedtwin.php?fref='+fpath;
	editWindow = pop(feurl,screen.availHeight,screen.availWidth);
}
function doEditImage(fpath) {
	popPost("imgedtwin.php", {"fref":fpath}, "imgedt", screen.availHeight, Math.min(1200,screen.availWidth));
}

function doFileAction(act,elem,evt) {
	evt.preventDefault();evt.stopPropagation();
	var fName = escape($(elem).parents('tr').attr('data-fref'));
	var fileoi = curDir+fName;
	var parms;
	switch (act) {
		case 'finf':
			parms = {
				act: act,
				fref: fileoi
				};
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
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

function doFillCLI(cmd) {
	$('#cmdlin').val(cmd);
}

function allSelect(evt, elem) {
	if (elem.checked) {
		$('.fsel').prop('checked',true);
	} else {
		$('.fsel').prop('checked',false);
	}
}

function fils2up() {
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

function selectionAction(fedt) {
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

$(function() {
	$("#fmnu [data-mnu]").click(function(e) {e.preventDefault(); doMenuAction($(this).attr('data-mnu'),e); });
	$("#trmfrm [data-mnu]").click(function(e) {e.preventDefault(); doMenuAction($(this).attr('data-mnu'),e); });
	$("#ftbl [data-act]").click(function(e) {e.preventDefault(); doFileAction($(this).attr('data-act'),this,e); });
	/*$('#fMrkDlg').dialog({
		autoOpen: false,
		width: 600,
		position: [200,100],
		buttons: {
			Okay: function() {
				$(this).dialog("close");
				},
			Clear: function() {
				sessionStorage.removeItem('fmx_mrkd');
				$(this).dialog("close");
				}
			}
		});*/
	$('nav li ul').hide().removeClass('fallback');
	$('nav li').hover(function () {
		$('ul', this).stop(true,true).fadeToggle(100);
	});
});
