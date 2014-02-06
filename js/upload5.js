// variables
var dropArea = document.getElementById('dropArea');
var fileInput = document.getElementById('upload_field');
var canvas = document.querySelector('canvas');
var context = canvas.getContext('2d');
var count = document.getElementById('count');
var destinationUrl = 'upload5.php';
var result = document.getElementById('result');
var list = [];
var totalSize = 0;
var totalProgress = 0;

// main initialization
(function(){

	var incmpl = 0; //tally of transfers yet to complete

	// init handlers
	function initHandlers () {
		dropArea.addEventListener('drop', handleDrop, false);
		dropArea.addEventListener('dragover', handleDragOver, false);
		dropArea.addEventListener('dragleave', handleDragLeave, false);
		fileInput.addEventListener('change', handleFileInput, false);
	}

	// draw progress
	function drawProgress (progress) {
		context.clearRect(0, 0, canvas.width, canvas.height); // clear context

		context.beginPath();
		context.strokeStyle = '#4B9500';
		context.fillStyle = '#4B9500';
		context.fillRect(0, 0, progress * 404, 20);
		context.closePath();

		// draw progress (as text)
		context.font = '16px Verdana';
		context.fillStyle = '#000';
		context.fillText('Progress: ' + Math.floor(progress*100) + '%', 50, 15);
	}

	function handleFileInput (event) {
		processFiles(this.files);
	}

	// drag over
	function handleDragOver (event) {
		event.stopPropagation();
		event.preventDefault();

		//dropArea.className = 'hover';
		this.classList.add('hover');
	}

	// drag leave
	function handleDragLeave (event) {
		event.stopPropagation();
		event.preventDefault();

		//dropArea.className = 'hover';
		this.classList.remove('hover');
	}

	// drag drop
	function handleDrop (event) {
		event.stopPropagation();
		event.preventDefault();

		processFiles(event.dataTransfer.files);
	}

	// process bunch of files
	function processFiles (filelist) {
		if (!filelist || !filelist.length || list.length) return;

		totalSize = 0;
		totalProgress = 0;
		result.textContent = '';

		for (var i = 0; i < filelist.length && i < 20; i++) {
			list.push(filelist[i]);
			totalSize += filelist[i].size;
			incmpl++;
		}
		uploadNext();
	}

	// on complete - start next file
	function handleComplete (size) {
		totalProgress += size;
		drawProgress(totalProgress / totalSize);
		uploadNext();
	}

	// update progress
	function handleProgress (event) {
		var progress = totalProgress + event.loaded;
		drawProgress(progress / totalSize);
	}

	// upload file
	function uploadFile (file, status) {

		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', destinationUrl);
		xhr.onload = function() {
			result.innerHTML += this.responseText;
			//handleComplete(file.size);

			// if all are complete, refresh the parent list
			if (!--incmpl) parent.opener.refreshFilst();
		};
		xhr.onerror = function () {
			result.textContent = this.responseText;
			handleComplete(file.size);
		};
		xhr.upload.onprogress = function (event) {
			handleProgress(event);
		};
		xhr.upload.onloadstart = function (event) {
			//alert('loadStart');
		};
		xhr.upload.onload = function (event) {
			//alert('load');
			//result.innerHTML += this.responseText;
			handleComplete(file.size);
		};
		xhr.upload.onerror = function (event) {
			alert('error');
		};
		xhr.upload.onabort = function (event) {
			alert('abort');
		};

		// prepare FormData
		var formData = new FormData();
		formData.append('user_file[]', file);
		formData.append('fpath',filesPath);
		xhr.send(formData);
	}

	// upload next file
	function uploadNext() {
		if (list.length) {
			count.textContent = list.length - 1;
			dropArea.className = 'uploading';

			var nextFile = list.shift();
			if (nextFile.size >= uploadMaxFilesize) {
				result.innerHTML += '<div class="f">File too large (max filesize exceeded)</div>';
				handleComplete(nextFile.size);
			} else {
				uploadFile(nextFile, status);
			}
		} else {
			dropArea.className = '';
		}
	}

	initHandlers();
})();