(function(w) {

	var totProgressDiv,
		totProgressDivW,
		progressDiv,
		totalProgressElem,
		qCountSpan,

		upQueue = [],
		maxXfer = 3,
		qStopt = false,
		inPrg = 0,
		total2do = 0,
		totalDone = 0,
		errCount = 0,
		e_st, e_gc,
		s_hd = 'none',
		s_vu = 'inline-block',
		queueCtrl = {
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

	// getElementById
	function $id(id) {
		return document.getElementById(id);
	}

	// insert message in progress element
	function insertElemMsg(elem,msg,asErr) {
		elem.innerHTML += msg;
		if (asErr) {
			elem.className = 'failure';
			errCount++;
		}
	}

	// file drag hover
	function FileDragHover(e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}

	// file selection
	function FileSelectHandler(e) {

		// here is a good spot to get the tot progress div width
		totProgressDivW = totProgressDiv.offsetWidth;

		// cancel event and hover styling
		FileDragHover(e);

		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;

		// process all File objects
		for (var i = 0, f; (f = files[i]); i++) {
			total2do += f.size;
			upQueue.push(f);
			qCountSpan.innerHTML = upQueue.length;
			NextInQueue(false,'fsel');
		}
		if (upQueue.length) e_st.style.display = s_vu;
	}

	function _endUp() {
		if (!qStopt) {
			allDone = 1;
			if (typeof(fup_done == 'function')) fup_done(errCount);
		}
	}

	function NextInQueue(decr,tag) {
		if (decr) {
			if (! --inPrg) { _endUp(); }
		}
		if (!qStopt && upQueue.length && (!maxXfer || inPrg < maxXfer)) {
			new UploadFileObj(upQueue.shift());
			inPrg++;
			qCountSpan.innerHTML = upQueue.length;
		}
		if (upQueue.length <= 0) {
			e_st.style.display = s_hd;
			e_gc.style.display = s_hd;
		}
	}

	function UpdateTotalProgress(adsz) {
		if (!totProgressDiv) return;
		totalDone += adsz;
		var p = Math.floor(totProgressDivW * totalDone / total2do) - 800;
		totProgressDiv.style.backgroundPosition = p + "px 0";
	}

	function UploadFileObj (file) {
		var self = this;
		var errM = null;

		this.lastsz = 0;
		this.fsize = file.size;

		this.doAbort = function() {
			if (this.xhr) this.xhr.abort();
			else progressDiv.removeChild(this.progress);
		};

		this.xhr = new XMLHttpRequest();
		if (this.xhr.upload) {

			this.xhr.upload.onabort = function(evt) {
				UpdateTotalProgress(self.fsize - self.lastsz);
			};

			this.xhr.upload.onloadstart = function(evt) {
				this.onprogress = function(e) {
					if (!e.lengthComputable) return;
					var p = Math.floor(self.progressW * e.loaded / e.total) - 800;
					self.progress.style.backgroundPosition = p + "px -20px";
					if (e.loaded == e.total) {
						self.progress.innerHTML = file.name;
						self.progress.className = 'indeterm';
						self.progress.style.backgroundPosition = "";
					}
					UpdateTotalProgress(e.loaded - self.lastsz);
					self.lastsz = e.loaded;
					};
			};

			if (typeof(fup_ftypes) == 'object' && fup_ftypes.indexOf(file.type) < 0) {
				errM = 'Cannot upload a file of this type. ('+file.type+')';
			} else if (file.size > $id("MAX_FILE_SIZE").value) {
				errM = 'File is larger than max size allowed.';
			}

			// create progress bar
			this.progress = progressDiv.appendChild(document.createElement("p"));
			this.progress.appendChild(document.createTextNode(file.name));
			this.progress.innerHTML = this.progress.innerHTML + '<img src="'+ fmx_appPath +'css/redX.png" class="abortX" onclick="AbortUpload(this);" />';
			this.progressW = this.progress.offsetWidth;
			this.progress._upld = this;

			if (errM) {
				insertElemMsg(this.progress, '<br />'+errM, true);
				UpdateTotalProgress(file.size);
				NextInQueue(true,'errM');
				return;
			}

			// file received/failed
			this.xhr.onreadystatechange = function(e) {
				var msg = '', rp;
				if (self.xhr.readyState == 4) {
					// check result
					if (self.xhr.status == 200) {
						rp = self.xhr.responseText;
						msg = rp == 'Ok' ? '' : ('<br />'+rp);
					}
					else if (self.xhr.status === 0) msg += '<br />-- aborted';
					else msg += '<br />' + self.xhr.status + ': ' + self.xhr.statusText;

					if (msg) {insertElemMsg(self.progress,msg,true)}
					else {progressDiv.removeChild(self.progress)}
					NextInQueue(true,'rst');	//console.log(e);
				}
			};

			// start upload
			this.xhr.open("POST", fmx_appPath+'upload5.php', true);
			if (fup_payload) {
				var formData = new FormData();
				formData.append('user_file[]', file);
				for (var key in fup_payload) {
					formData.append(key, fup_payload[key]);
				}
				this.xhr.send(formData);
			} else {
				this.xhr.setRequestHeader("Content-Type", "application/octet-stream");
				this.xhr.setRequestHeader("X_FILENAME", file.name);
				this.xhr.send(file);
			}
		}
	}

	// initialize
	function Init() {

		var fileselect = $id("upload_field"),
			filedrag = $id("dropArea"),
			submitbutton = $id("submitbutton"),
			xhr;

		qCountSpan = $id("qCount");
		e_st = $id('qstop');
		e_gc = $id('qgocan');

		// file select
		if (fileselect) fileselect.addEventListener("change", FileSelectHandler, false);

		// is XHR2 available?
		xhr = new XMLHttpRequest();
		if (xhr.upload) {

			// file drop
			filedrag.addEventListener("dragover", FileDragHover, false);
			filedrag.addEventListener("dragleave", FileDragHover, false);
			filedrag.addEventListener("drop", FileSelectHandler, false);
			filedrag.style.display = "block";

			// remove submit button
			if (submitbutton) submitbutton.style.display = "none";

			// progress display area
			totProgressDiv = $id("totprogress");
			progressDiv = $id("fprogress");
		}
		xhr = null;

	}

	// call initialization file
	if (window.File && window.FileList) {
		Init();
	}

	w.fupQctrl = queueCtrl;

})(window);

function AbortUpload (node) {
	node.parentNode._upld.doAbort();
}
