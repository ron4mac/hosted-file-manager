/* globals $,RJ_DlogMgr */
/* exported rjOpenDlg,rjCloseDlg,rjHtmlElement */
'use strict';

var fmx_ui = {
	ddm: null,
	mdl: null
}

// apply esc and return keys to the modal
//$(function() {
//	$(document).keyup(function(e) {
//		var mdlg = $('#element_to_pop_up');
//		if (!mdlg[0]._jqmShown) return;
//		if (e.keyCode == 13) { $('.btn-prm', mdlg).click(); }		// enter
//		if (e.keyCode == 27) { mdlg.jqmHide(); }		// esc
//	});
//});

function rjOpenDlg (e, bid, opts={}) {
	if (e) { e.preventDefault(); }
	console.log('bid2: '+bid);
	RJ_DlogMgr.hoistTmpl(bid, opts);
}
function rjCloseDlg (rjdlg, rslt) {
	rjdlg.close(rslt);
}

function rjHtmlElement (tag, attribs, inner, style) {
	let elm = document.createElement(tag);
	if (attribs) {
		for (const [key, value] of Object.entries(attribs)) {
			elm.setAttribute(key, value);
		}
	}
	if (inner) {
		elm.innerHTML = inner;
	}
	if (style) {
		for (const [key, value] of Object.entries(style)) {
			elm.style[key] = value;
		}
	}
	return elm;
}

_rj.ae(document,'click',e=>{
	if (fmx_ui.ddm) {
		fmx_ui.ddm.style.display = 'none';
		fmx_ui.ddm = null;
	}
});