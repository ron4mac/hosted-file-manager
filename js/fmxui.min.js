// creates UI dialogs using jqModal

// apply esc and return keys to the modal
$(function() {
	$(document).keyup(function(e) {
		var mdlg = $('#element_to_pop_up');
		if (!mdlg[0]._jqmShown) return;
		if (e.keyCode == 13) { $('.btn-prm', mdlg).click(); }		// enter
		if (e.keyCode == 27) { mdlg.jqmHide(); }		// esc
	});
});


function MyDlg (dObj) {

	// do a selfie
	var self = this;
	// save our dialog object
	this.dObj = dObj;

	// create the dialog
	this.dlg = document.createElement("DIV");
	this.dlg.className = "jqmWindow";
	this.dlg.innerHTML = '<div class="mdlg_hdr">HEADER</div><div class="mdlg_ctn">CONTENT<input type="text" name="ttxt" value="XYZ123" /></div><div class="mdlg_ftr">FOOTER</div>';
	document.body.appendChild(this.dlg);
	$(this.dlg).jqm({onHide:function(h){h.w.hide() && h.o && h.o.remove();document.body.removeChild(self.dlg);return false}}).jqmShow();
}


function myOpenDlg (e, dId, fVals, titl) {	console.log(dId);
	e.preventDefault();
	// get the dialog
	var dlg = $('#element_to_pop_up');
	// clean out any previous data
	dlg.removeData();
	// get the buttons element
	var btnhtm = $('div.bp-bttns', dlg);
	// clear out any existing buttons
	$(btnhtm).empty();
	// if there are buttons, insert them
	var key,btn,n_c;
	if (dId.buttons) {
		for (key in dId.buttons) {
			// create the button
			btn = document.createElement('button');
			// bind its click action
			$(btn).on('click', dId.buttons[key]);
			// see if there is a button name override
			if (dId.bover && (key in dId.bover)) {
				key = dId.bover[key]; 
			}
			// split out name and class
			n_c = key.split('`');
			// set its class
			btn.className = 'btn-' + (n_c[1] ? n_c[1] : 'scd');
			// insert its content
			$(btn).html(n_c[0]);
			// insert the new button in the buttons element
			$(btnhtm).append(btn);
		}
	}
	var tmpl = $(dId.cselect);
	if (dId.loadUrl) {
		// insert the dialog title
		$('span.bpDlgTtl', dlg).html(tmpl.attr('title'));
		// insert the dialog content
		$('form.bp-dctnt', dlg).html(tmpl.html());
		// display the dialog
		$(dlg).jqm({ajax:dId.loadUrl,target:$('form.bp-dctnt',dlg),modal:true}).jqmShow();
	} else {
		// insert the dialog title
		$('span.bpDlgTtl', dlg).html(titl || tmpl.attr('title'));
		// insert the dialog content (after substitutions)
		var fhtm = tmpl.html();
		if (fVals) {
			var re;
			for (var f in fVals) {
				re = new RegExp('{'+f+'}','g');
				fhtm = fhtm.replace(re, fVals[f]);
			}
		}
		$('form.bp-dctnt', dlg).html(fhtm);
		// display the dialog
		$(dlg).jqm({overlay:20,modal:dId.modal}).jqmShow();
		// fill in fields as provided
//		if (fVals) {
//			var frm = $('form.bp-dctnt', dlg).get(0);	console.log(frm);
//			for (var f in fVals) {	alert(f+':'+fVals[f]);
//				//frm.f.value = fVals[f];
//				document.myUIform.f.value = fVals[f];
//			}
//		}
	}
}

function myProcessDlg (elem, action) {
	var clos = true;
	if (action) {
		var frm = $(elem).parent().parent().prev().children(":first");
		var ddat = $(frm).serialize();
		clos = action($(frm).get(0), ddat);
	}
	if (clos) myCloseDlg(elem);
}

function myCloseDlg (elem) {
	// close the elements dialog container
	$(elem).closest('.jqmWindow').jqmHide();
}
