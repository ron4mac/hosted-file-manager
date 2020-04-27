/* uplodr v0.8 */

/* a couple of utility functions to avoid using jquery and assist in minification */
// getElementById
function $id (id) {
	return document.getElementById(id);
}
// addEventListener
function $ae (elem, evnt, func) {
	elem.addEventListener(evnt, func, false);
}
 
/* encapsulate the entire upload engine in a function */
(function (w, FLDS, CFG) {

	const defaults = {
			lodrdiv: 'uplodr',
			upURL: 'upload.php',
			payload: {},	// other data sent along with the file data
			maxFilesize: 134217728,
			dropMessage: 'Drop files here to upload<br>(or click to select)',
			concurrent: 3,
			maxchunksize: 16777216,		// 16M
			allowed_file_types: [],	// all
			doneFunc: null
		};

	var opts = Object.assign({}, defaults, CFG),
		totProgressBar,
		progressDiv,
		qCountSpan,

		upQueue = [],
		maxXfer = opts.concurrent,
		aft = opts.allowed_file_types,
		maxcnksz = opts.maxchunksize,
		qStopt = false,
		inPrg = 0,
		total2do = 0,
		totalDone = 0,
		allDone = 0,
		okCount = 0,
		errCount = 0,
		e_st, e_gc,
		s_hd = 'none',
		s_vu = 'inline-block',
		slfunc = '',
		_qCtrl = {
			stop: function () {
				qStopt = true;
				e_st.style.display = s_hd;
				e_gc.style.display = s_vu;
				},
			go: function () {
				qStopt = false;
				e_st.style.display = s_vu;
				e_gc.style.display = s_hd;
				while (upQueue.length && (inPrg < maxXfer)) NextInQueue(false,'go');
				},
			cancel: function () {
				upQueue.length = 0;
				qStopt = false;
				e_gc.style.display = s_hd;
				qCountSpan.innerHTML = 0;
				if (!inPrg) _endUp();
				}
			}
		;

	// utility element creator
	function CreateElement (type, cont, attr) {
		var elem = document.createElement(type, attr);
		if (cont) elem.innerHTML = cont;
		for (var key in attr) {
			elem.setAttribute(key, attr[key]);
		}
		return elem;
	}

	// file drag hover
	function FileDragHover (e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == 'dragover' ? 'hover' : 'dropzone');
	}

	// file selection
	function FileSelectHandler (e) {
		var files, i, f;

		if (e instanceof FileList) {
			files = e;
		} else {
			// cancel event and hover styling
			FileDragHover(e);

			// fetch FileList object
			files = e.target.files || e.dataTransfer.files;
		}

		if (allDone) allDone = total2do = totalDone = 0;

		// process all File objects
		for (i = 0; (f = files[i]); i++) {
			total2do += f.size;
			upQueue.push(f);
			qCountSpan.innerHTML = upQueue.length;
			NextInQueue(false,'fsel');
		}
		if (upQueue.length > maxXfer) e_st.style.display = s_vu;
	}

	function _endUp () {
		if (!qStopt) {
			allDone = 1;
			if (typeof(opts.doneFunc) == 'function') {
				let okC = okCount, errC = errCount;
				setTimeout(function(){ opts.doneFunc(okC, errC); }, 500);
			}
			errCount = okCount = 0;
		}
	}

	function NextInQueue (decr,tag) {
		if (decr) {
			if (tag == 'ufo') okCount++;
			if (! --inPrg) { _endUp(); }
		}
		if (!qStopt && upQueue.length && (!maxXfer || inPrg < maxXfer)) {
			var nxf = upQueue.shift();
			var ufo = new UpldFileObj(nxf);			//console.log(ufo);
			inPrg++;
			qCountSpan.innerHTML = upQueue.length;
		}
		if (upQueue.length <= 0) {
			e_st.style.display = s_hd;
			e_gc.style.display = s_hd;
		}
	}

	// progress bar object
	function ProgressBar (fileObj, sclass) {
		let $ = this;
		
		$.show = function (percent) {
			let p = 100 * percent;
			$.pb.style.width = p + "%";
			if (percent === 1) {
				$.pb.className = 'indeterm';
			}
		};
		$.msg = function (msg, err) {
			$.pbi.innerHTML += '<br />' + msg;
			if (err) {
				$.pbi.className = 'pbfinf failure';
				errCount++;
			}
		};
		$.remove = function () {
			$.pbw._ufo = null;
			progressDiv.removeChild($.pbw);
			$.fObj = null;
		};

		// create progress bar
		let pbw = CreateElement('div', '', {class:'pbwrp'});
		$.pb = pbw.appendChild(CreateElement('div', '', {class:sclass}));
		let pbv = fileObj.fileName + '<i class="fa fa-window-close abortX" aria-hidden="true" onclick="this.parentNode.parentNode._ufo.abort(true);"></i>';
		$.pbi = pbw.appendChild(CreateElement('div', pbv, {class:'pbfinf'}));
		progressDiv.appendChild(pbw);
		$.pbw = pbw;
		$.pbw._ufo = fileObj;
		$.fObj = fileObj;
		return this;
	}

	function UpdateTotalProgress (adsz) {
		if (!totProgressBar) return;
		if (adsz < 0) return;
		totalDone += adsz;
		var wp = 100 * totalDone / total2do;
		totProgressBar.style.width = wp + "%";
	}

	function addData (frmd, data) {
		for (var key in data) {
			frmd.append(key, data[key]);
		}
	}

	// object for a file upload with chunking support
	function UpldFileObj (file) {
		var $ = this, key, query;
		$.upFile = file;
		$.fileName = file.fileName || file.name;
		$.size = file.size;
		$.upState = '';
		$.doChnk = ($.upFile.size > maxcnksz);
		$.chnkSize = Math.round(maxcnksz / 2) - 3072;
		$.relPath = file.webkitRelativePath || $.fileName;
		$.uniqueId = $.size + '-' + $.relPath.replace(/[^0-9a-zA-Z_-]/img, '');
		$.actSize = 0;
		$.startByte = 0;
		$.lastsz = 0;
		$.numChnks = Math.max(Math.floor($.size / $.chnkSize), 1);
		$.chnkNum = 0;
		$.fData = null;
		$.upForm = {};				// extra data can be added
		$.xhr = new XMLHttpRequest();

		var endup = function (all) {
			if ($.xhr) {
				$.xhr.upload.onprogress = null;
				$.xhr.onabort = null;
				$.xhr.onerror = null;
				$.xhr.onload = null;
				$.xhr = null;
			}
			$.fData = null;
			if (all && $.pBar) {
				$.pBar.remove();
				$.pBar = null;
			}
			NextInQueue(true,'ufo');
		};

		var fDat = function () {
			$.fData = new FormData();
			addData($.fData, opts.payload);
		};

		var state = function () {
			fDat();
			switch ($.upState) {
				case '':
					addData($.fData, $.upForm);
					$.fData.append('Filedata', $.upFile);
					$.upState = 'upld';
					break;
				case 'upld':
					endup(true);
					return;
					break;
			}
			$.xhr.open('POST', opts.upURL);
			$.xhr.send($.fData);
		};

		var cstate = function () {
			fDat();
			switch ($.upState) {
				case '':
					//console.log('pref',$.fileName);
					addData($.fData, { chunkact: 'pref', file: $.fileName, size: $.size, ident: $.uniqueId});
					$.upState = 'chnk';
					break;
				case 'chnk':
					//console.log('chnk',$.chnkNum+1,$.fileName);
					addData($.fData, { chunkact: 'chnk', ident: $.uniqueId, fname: $.fileName, tchnk: $.numChnks });
					if ($.chnkNum == $.numChnks) { endup(true); return; }	///////// do stuff here to finish up
					$.startByte = $.chnkNum * $.chnkSize;
					$.endByte = Math.min($.size, ($.chnkNum + 1) * $.chnkSize);
					if ($.size - $.endByte < $.chnkSize) {
						// The last chunk will be bigger than the chunk size, but less than 2*chunkSize
						$.endByte = $.size;
					}
					$.actSize = $.endByte - $.startByte;
					$.fData.append('chnkn', ++$.chnkNum);
					if ($.chnkNum == $.numChnks) {
						addData($.fData, $.upForm);
					}
					$.fData.append('Filedata', $.upFile[slfunc]($.startByte, $.endByte));
					$.lastsz = 0;
					break;
				case 'abrt':
					//console.log('abrt',$.fileName);
					$.xhr.timeout = 10000;
					addData($.fData, { chunkact: 'abrt', ident: $.uniqueId, fname: $.fileName });
					$.upState = 'nil';
					break;
				case 'nil':
					//console.log('nil ',$.fileName);
					endup();
					return;
					break;
			}
			//console.log($.chnkNum, $.fileName);
			$.xhr.open('POST', opts.upURL);
			$.xhr.send($.fData);
		};

		var cb = {
			prog: function (e) {
					if (!e.lengthComputable) return;
					var loded = Math.round(e.loaded / e.total * $.actSize);
					if ($.upState == 'chnk' && $.chnkNum) {		//console.log($.actSize,loded,e);
						//var loded = $.loaded;
						$.pBar.show(($.startByte + loded) / $.size);
						UpdateTotalProgress(loded - $.lastsz);
						$.lastsz = loded;
					} else if ($.upState == 'upld') {
						$.pBar.show(e.loaded / e.total);
						loded = Math.round(e.loaded / e.total * $.size);
						UpdateTotalProgress(loded - $.lastsz);
						$.lastsz = loded;
					}
				},
			chng: function (e) {
				//	console.log(this,e);
					if (this.readyState < 4) { return; }
					if (this.status !== 200) {
						UpdateTotalProgress($.size - $.startByte - $.lastsz);
						$.lastsz = $.size;
						if (this.status === 0) {
							$.pBar.msg('-- Aborted', true);
							if ($.doChnk) {
								$.upState = 'abrt';
								cstate();
							} else {
								endup();
							}
						} else {
							$.pBar.msg(this.responseText || this.statusText || this.status, true);
							endup();
						}
					} else if (this.status === 200) {
						if (this.responseText.length) {
							$.pBar.msg(this.responseText, true);
						} else {
							if ($.doChnk) {
								cstate();
							} else {
								state();
							}
						}
					}
				},
			fail: function (e) {
					$.pBar.msg(this.responseText, true);
					//console.log(e,this);
				}
			};

		$.abort = function (ua) {
			if ($.xhr) {
				var xrs = $.xhr.readyState;
				if (xrs < 4 && xrs !== 0) {
					$.xhr.abort();
				} else {
					cb.abrt();
				}
			} else {
				$.pBar.remove();
				$.pBar = null;
			}
		};

		// put up the progress bar
		$.pBar = new ProgressBar($, $.doChnk ? 'chnkpb' : 'normpb');

		var errM = '';
		if (typeof(aft) == 'object' && aft.length) {
			var dotParts = file.name.split('.');
			if (dotParts.length == 1 || (aft.indexOf(dotParts.pop().toLowerCase()) < 0)) {
				errM = '<i class="fa fa-info-circle infoG" onclick="alert(\'Allowed file types: \' + H5uOpts.allowed_file_types.join(\', \'));"></i> File type not allowed';
			}
		} else if (file.size > opts.maxfilesize) {
			errM = 'File is larger than allowed';
		}

		if (errM) {
			$.pBar.msg(errM, true);
			UpdateTotalProgress(file.size);
			$.xhr = null;
			NextInQueue(true,'errM');
			return;
		}

		$.xhr.onreadystatechange = cb.chng;
		$.xhr.upload.onerror = cb.fail;
		$.xhr.upload.onprogress = cb.prog;

		if ($.doChnk) {
			cstate();
		} else {
			state();
		}

		return (this);
	}

	function _setup () {
		let updiv = $id(opts.lodrdiv);
		if (w.File && w.FileList) {
			// create UI
			updiv.appendChild(CreateElement(
				'div',
				'<input type="file" name="userpictures" id="file_field" multiple="multiple" accept="image/*,video/*" style="display:none">'
				+ '<div class="drpmsg">'+opts.dropMessage+'</div>',
				{id:'dropArea', onclick:"$id('file_field').click();"}
				)
			);
	
			let uprg = '<div id="progress_report_name"></div><div id="progress_report_status" style="font-style: italic;"></div><div id="totprogress"><div id="progress_report_bar"></div></div><div>Files queued: <span id="qcount">0</span><div class="acti" id="qstop"><i class="fa fa-pause-circle pausQ" title="stop queue" onclick="H5uQctrl.stop()"></i></div><div class="acti" id="qgocan"><i class="fa fa-play-circle playQ" title="resume queue" onclick="H5uQctrl.go()"></i><i class="fa fa-times cancelQ" title="cancel queue" alt="" onclick="H5uQctrl.cancel()"></i></div></div><div id="fprogress"></div><div id="server_response"></div>';
			updiv.appendChild(CreateElement('div', uprg, {id:'progress_report', style:'position:relative'}));

			qCountSpan = $id('qcount');
			e_st = $id('qstop');
			e_gc = $id('qgocan');

			// file select
			$ae($id('file_field'), 'change', FileSelectHandler);

			// is XHR2 available?
			let xhr = new XMLHttpRequest();
			if (xhr.upload) {

				// file drop
				let filedrag = $id('dropArea');
				$ae(filedrag, 'dragover', FileDragHover);
				$ae(filedrag, 'dragleave', FileDragHover);
				$ae(filedrag, 'drop', FileSelectHandler);
				filedrag.style.display = 'block';

				// progress display area
				totProgressBar = $id('progress_report_bar');
				progressDiv = $id('fprogress');
			}
			xhr = null;

			// establish slicing function for chunking
			if ((typeof(Blob)!=='undefined')) {
				slfunc = (!!Blob.prototype.slice ? 'slice' : (!!Blob.prototype.webkitSlice ? 'webkitSlice' : (!!Blob.prototype.mozSlice ? 'mozSlice' : '')));
			}
		} else {
			updiv.appendChild(CreateElement('div', 'Can not use this upload method with the web browser that you are using.', {}));
		}
	}

	w.H5uSetup = _setup;
	w.H5uQctrl = _qCtrl;
	w.H5uOpts = opts;
	w.fupQadd2 = FileSelectHandler;


})(window, [], h5uOptions||{});

