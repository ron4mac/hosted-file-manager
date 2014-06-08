(function() {

	var totProgressDiv,
		totProgressDivW,
		progressDiv,
		totalProgressElem,
		countSpan,

		upQueue = [],
		maxXfer = 3,
		inPrg = 0,
		total2do = 0,
		totalDone = 0;

	// getElementById
	function $id(id) {
		return document.getElementById(id);
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
			countSpan.innerHTML = upQueue.length;
			NextInQueue(false,'fsel');
		}
	}

	function NextInQueue(decr,tag) {
		if (decr) {
			if (! --inPrg) {
				if (typeof(fup_done == 'function')) fup_done();
			}
		}
		if (upQueue.length && (!maxXfer || inPrg < maxXfer)) {
			var ufo = new UploadFileObj(upQueue.shift());
			inPrg++;
			countSpan.innerHTML = upQueue.length;
		}
	}

	function UpdateTotalProgress(adsz) {
		if (!totProgressDiv) return;
		totalDone += adsz;
//		var pc = Math.max(parseInt(100 - (totalDone / total2do * 100), 10), 0);
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
					//var pc = parseInt(100 - (e.loaded / e.total * 100), 10);
//					var pc = e.loaded / e.total;
					var p = Math.floor(self.progressW * e.loaded / e.total) - 800;
					self.progress.style.backgroundPosition = p + "px 0";
//					if (pc === 0) {
					if (e.loaded == e.total) {
						self.progress.innerHTML = file.name;
						self.progress.className = 'indeterm';
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
			this.progress.innerHTML = this.progress.innerHTML + '<img src="css/redX.png" class="abortX" onclick="AbortUpload(this);" />';
			this.progressW = this.progress.offsetWidth;
			this.progress._upld = this;

			if (errM) {
				this.progress.innerHTML = this.progress.innerHTML + '<br />' +errM;
				this.progress.className = "failure";
				UpdateTotalProgress(file.size);
				NextInQueue(true,'errM');
				return;
			}

			// file received/failed
			this.xhr.onreadystatechange = function(e) {
				if (self.xhr.readyState == 4) {
					self.progress.className = (self.xhr.status == 200 ? "success" : "failure");
					// on good result, remove progress bar
					if (self.xhr.status == 200) progressDiv.removeChild(self.progress);
					else if (self.xhr.status === 0) self.progress.innerHTML = self.progress.innerHTML + '<br />-- aborted';
					else self.progress.innerHTML = self.progress.innerHTML + '<br />' + self.xhr.status + ': ' + self.xhr.statusText;
					//self.xhr = null;
					NextInQueue(true,'rst');	//console.log(e);
				}
			};

			// start upload
			this.xhr.open("POST", 'upload5.php'/*$id("upload").action*/, true);
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
			submitbutton = $id("submitbutton");

		// file select
		if (fileselect) fileselect.addEventListener("change", FileSelectHandler, false);

		// is XHR2 available?
		var xhr = new XMLHttpRequest();
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
			countSpan = $id("qCount");
		}
		xhr = null;

	}

	// call initialization file
	if (window.File && window.FileList) {
		Init();
	}

})();

function AbortUpload (node) {
	node.parentNode._upld.doAbort();
}
