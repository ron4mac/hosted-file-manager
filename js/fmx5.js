//$(document).ready(function() {
$(function() {
$("#fmnu [data-mnu]").click(function(e) {e.preventDefault(); doMenuAction($(this).attr('data-mnu'),e); });
$("#trmfrm [data-mnu]").click(function(e) {e.preventDefault(); doMenuAction($(this).attr('data-mnu'),e); });
$("#ftbl [data-act]").click(function(e) {e.preventDefault(); doFileAction($(this).attr('data-act'),this,e); });
//$(".fsel").change(function() { handleFileSelect(); });
$('#aMsgDlg').dialog({
	autoOpen: false,
	width: 600,
	position: [200,100],
	buttons: {
		Okay: function() {
			$(this).dialog("close");
			},
		Cancel: function() {
			$(this).dialog("close");
			}
		}
	});
$('#fMrkDlg').dialog({
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
	});
$('#fRenDlg').dialog({
	autoOpen: false,
	width: 400,
	position: [100,100],
	buttons: {
		Cancel: function() {
			$(this).dialog("close");
			},
		Rename: function() {
			parms = {
				act: 'fren',
				fref: curDir + $('#oldnm').val(),
				nunm: curDir + $('#nunam').val()
				};
			$(this).dialog("close");
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data) }
					else refreshFilst();
					});
			}
		}
	});
$('#fCpyDlg').dialog({
	autoOpen: false,
	width: 500,
	position: [100,100],
	buttons: {
		Cancel: function() {
			$(this).dialog("close");
			},
		Copy: function() {
			//cpyfnam = $('#cpyfnm').val();
			//cpy2nam = $('#cpy2nam').val();
			//parms = 'act=copy&fref='+cpyfnam+'&tonm='+cpy2nam; //alert(parms);
			parms = {
				act: 'copy',
				fref: $('#cpyfnm').val(),
				tonm: $('#cpy2nam').val()
				};
			$(this).dialog("close");
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data) }
					else refreshFilst();
					});
			}
		}
	});
$('#fMovDlg').dialog({
	autoOpen: false,
	width: 500,
	position: [100,100],
	buttons: {
		Cancel: function() {
			$(this).dialog("close");
			},
		Move: function() {
			//movfnam = $('#movfnm').val();
			//mov2nam = $('#mov2nam').val();
			//parms = 'act=move&fref='+movfnam+'&tonm='+mov2nam; //alert(parms);
			parms = {
				act: 'move',
				fref: $('#movfnm').val(),
				tonm: $('#mov2nam').val()
				};
			$(this).dialog("close");
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data) }
					else refreshFilst();
					});
			}
		}
	});
$('#fNamDlg').dialog({
	autoOpen: false,
	width: 400,
	position: [100,100],
	buttons: {
		Cancel: function() {
			$(this).dialog("close");
			},
		Create: function() {
			parms = {
				act: $('#ffact').val(),
				fref: curDir + $('#ffnam').val()
				};
			$(this).dialog("close");
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data) }
					else refreshFilst();
					});
			}
		}
	});

$('nav li ul').hide().removeClass('fallback');
$('nav li').hover(function () {
	$('ul', this).stop(true,true).fadeToggle(100);
});
	
});	//end document ready


function refreshFilst() {
	window.location.reload();
}

function doMenuAction(cmd,evt) {
	var slctd = $(".fsel:checked");
	var scnt = slctd.length;
	var parms, curfn, trmFrm, destfn;
	var oneItem = function () { if (!scnt) {alert('An item needs to be selected')} else if (scnt>1) {alert('Please select only one item.')} else return true; return false; };
	var hasSome = function () { if (scnt) return true; alert('Some items need to be selected'); return false; };
	switch (cmd) {
	case 'copy':
		if (oneItem()) {
			curfn = curDir+$(slctd[0]).parents('tr').attr('data-fref');
			$('#cpyfnm').val(curfn);
			$('#cpyfnam').html('From: '+curfn);
			$('#cpy2nam').val(curfn);
			$('#fCpyDlg').dialog('open');
			}
		break;
	case 'cppa':
		if (evt.altKey) {
			alert(sessionStorage.fmx_cppa);
			break;
		}
		if (scnt) {
			sessionStorage.fmx_cppa = $("form[name='filst']").serialize();
		}
		else {
			parms = 'act=cppa&todr='+encodeURIComponent(curDir)+'&'+sessionStorage.fmx_cppa;
			//alert(parms);
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data) }
					else refreshFilst();
					});
				}
		break;
	case 'delf':
		if (!hasSome()) break;
		if (!confirm('Are you sure you want to delete the selected files?')) break;
		parms = 'act=delf&'+$("form[name='filst']").serialize();
		$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
				if (data) { alert(data) }
				else refreshFilst();
				});
		break;
	case 'dnld':
		if (!hasSome()) break;
		parms = 'act=dnld&'+$("form[name='filst']").serialize();
		$('div.dnldprg').css('display','inline');
		$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
				$('div.dnldprg').css('display','none');
				if (data) { downloadFile(data.fpth,data.rad=='Y',false); }
				else alert('download not available');
				}
				,'json');
		break;
	case 'dupl':
		if (oneItem()) {
			parms = {
				act: 'dupl',
				fref: curDir + $(slctd[0]).parents('tr').attr('data-fref')
				};
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data) }
					else refreshFilst();
					});
			}
		break;
	case 'mark':
		//console.log(evt);
		parms = sessionStorage.fmx_mrkd ? (sessionStorage.fmx_mrkd+"\0") : '';
		if (scnt) {
			sessionStorage.fmx_mrkd = parms + $("form[name='filst']").serialize();
		} else {
			$('#fMrk').html(sessionStorage.fmx_mrkd ? makeFileList(sessionStorage.fmx_mrkd, true) : '&lt;empty&gt;');
			$('#fMrkDlg').dialog('open');
			//alert(sessionStorage.fmx_mrkd ? makeFileList(sessionStorage.fmx_mrkd) : 'empty');
			//if (evt.altKey) sessionStorage.removeItem('fmx_mrkd');
		}
		break;
	case 'move':
		if (oneItem()) {
			curfn = curDir+$(slctd[0]).parents('tr').attr('data-fref');
			$('#movfnm').val(curfn);
			$('#movfnam').html('Move: '+curfn);
			$('#mov2nam').val(curfn);
			$('#fMovDlg').dialog('open');
			}
		break;
	case 'mvto':
		if (scnt) {
			sessionStorage.fmx_mvto = $("form[name='filst']").serialize();
		}
		else {
			if (!sessionStorage.fmx_mvto) {
				alert('Nothing previously selected to move');
				break;
			}
			parms = 'act=mvto&todr='+encodeURIComponent(curDir)+'&'+sessionStorage.fmx_mvto;
			//alert(parms);
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) { alert(data) }
					else refreshFilst();
					});
				}
		break;
	case 'nfle':
	case 'nfld':
		$('#ffact').val(cmd);
		$('#fNamDlg').dialog( "option", "title", cmd == 'nfle' ? "New File" : 'New Folder').dialog('open');
		break;
	case 'refr':
		refreshFilst();
		break;
	case 'rnam':
		if (oneItem()) {
			curfn = $(slctd[0]).parents('tr').attr('data-fref');
			$('#oldnm').val(curfn);
			$('#nunam').val(curfn);
			$('#fRenDlg').dialog( "option", "title", 'Rename <u>'+curfn+'</u>').dialog('open');
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
		var trm = prompt('Search for '+prmt+':',strm);
		if (trm) {
			trmFrm = document.forms.cliterm;
			if (cmd=='srhf') {
				sessionStorage.fmx_strmf = trm;
				trmFrm.cmdlin.value = 'find ./ -name '+trm+' -ls';
			} else {
				sessionStorage.fmx_strmc = trm;
				trmFrm.cmdlin.value = 'grep -R -I '+trm+' *';
			}
			trmFrm.submit();
		}
		break;
	case 'upld':
		if (doesSupportAjaxUploadWithProgress()) {
			//popUp('filupld5.php?path='+curDir);

			//popUp('filupld5s.php?path='+curDir);
			sessionStorage.fmx_curD = curDir;
			//popUp('filupld5d.php?path='+curDir);
			popUp('filupld5d.php');
			}
		else popUp('filupld.php?path='+curDir);
		break;
	case 'uzip':
	case 'zip':
	case 'tarz':
	case 'utrz':
		if (oneItem()) {
			curfn = /*curDir+*/$(slctd[0]).parents('tr').attr('data-fref');
			//console.log(document.forms.cliterm);
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
				if (xyz) curfn = xyz;
				else break;
			}
			var wPath = curDir.slice(curDir.search("/"));
			//alert(wPath+curfn);
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
						//else refreshFilst();
						});
			} else alert('Must be an XML file');
			}
		break;
	case 'fmxi':
		parms = {act: 'fmxi'};
		$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
				if (data) { alert(data) }
				});
		break;
	case 'cmcs':
		console.log(evt);
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

function doFileAction(act,elem,evt) {
	evt.preventDefault();evt.stopPropagation();
	var fName = escape($(elem).parents('tr').attr('data-fref'));
	var fileoi = curDir+fName;
	var parms;
	switch (act) {
		case 'finf':
			//parms = 'act='+act+'&fref='+fileoi;
			parms = {
				act: act,
				fref: fileoi
				};
			$.post("fmxjx.php", parms, function(data,textStatus,jqXHR) {
					if (data) {
						$("#aMsg").html(data);
						$('#aMsgDlg').dialog('option','title','File info for: '+fName).dialog('open');
						}
					});
			break;
		case 'fvue':
			doViewFile(fileoi);
			break;
		case 'fedt':
			doEditFile(fileoi);
			break;
		default:
			alert('?'+act+'?');
			break;
	}
}

function doFillCLI(cmd) {
	$('#cmdlin').val(cmd);
}

function downloadFile(A, cdl, asf) {
	var dlURL = 'fildnld.php?fle=' + escape(A) + (cdl ? '&tcdl=1&rad=Y' : '') + (asf ? ('&asf='+asf) : ''); //alert(dlURL);
	//dlframe.location.href = dlURL;
	var dlframe = document.createElement("iframe");
	// set source to desired file
	dlframe.src = dlURL;
	// This makes the IFRAME invisible to the user.
	dlframe.style.display = "none";
	// Add the IFRAME to the page.  This will trigger the download
	document.body.appendChild(dlframe);
}

function pop(url, h1, w1) {
	var h2 = (screen.height-h1)/2;
	var w2 = (screen.width-w1)/2;
	var wcon="toolbar=no,status=no,location=no,menubar=no,resizable=0,scrollbars=1,width="+w1+",height="+h1+",left="+w2+",top="+h2;
	return open(url, "", wcon);
}

function popUp(url) {
	var win = pop(url,240,416);
}

function allSelect(evt, elem) {
	if (elem.checked) {
		$('.fsel').attr('checked',true);
	} else {
		$('.fsel').removeAttr('checked');
	}
}

function doesSupportAjaxUploadWithProgress() {

	function supportFileAPI() {
		var fi = document.createElement('INPUT');
		fi.type = 'file';
		return 'files' in fi;
	}

	function supportAjaxUploadProgressEvents() {
		var xhr = new XMLHttpRequest();
		return !! (xhr && ('upload' in xhr) && ('onprogress' in xhr.upload));
	}

	//return false;
	return supportFileAPI() && supportAjaxUploadProgressEvents();
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

function makeFileList(fstr, htm) {
	var sep = htm ? '<br />' : ' ';
	var rslt = '';	//return fstr;
	var itms = fstr.split("\0");	//console.log(itms);
	for (var itm in itms) {
		var fpts = itms[itm].split("&");	//console.log(fpts);
		var fdr = '';
		for (var fpt in fpts) {
			var fpa = fpts[fpt].split("=");	//console.log(fpa);
			if (fpt==0) { fdr = '/'+fpa[1] }
			else { rslt += fdr + '%2F' + fpa[1] + sep }
		}
	}
	return rslt.replace(/%2F/g,'/');
}

function selectionAction(fedt) {
	var sel = "";
	if (typeof window.getSelection != "undefined") {
		sel = window.getSelection();
	} else if (typeof document.selection != "undefined") {
		if (document.selection.type == "Text") {
			sel = document.selection.createRange().text;
		}
	}
	var fpth = curDir+sel;
	//var fedt = confirm("Edit this file? (otherwise will just view)");
	if (fedt) { doEditFile(fpth); }
	else { doViewFile(fpth); }
}