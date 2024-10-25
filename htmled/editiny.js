const useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
const isSmallScreen = window.matchMedia('(max-width: 1023.5px)').matches;
tinymce.init({
	license_key: 'gpl',
	promotion: false,
	selector: 'textarea#the_html',
	external_plugins: {
		savefile: baseURL+'/htmled/savefile/plugin.min.js'
	},
	plugins: 'preview savefile importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
	menu: {
		file: { title: 'File', items: 'openfile | savefile | newdocument restoredraft | preview | print' }
	},
	menubar: 'file edit view insert format tools table help',
	toolbar: "undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | lineheight outdent indent| forecolor backcolor removeformat | charmap emoticons | code fullscreen preview | save print | pagebreak anchor codesample | ltr rtl",
	setup: (editor) => {
		editor.ui.registry.addIcon(
			'savefile','<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g><path d="M11.3,15.7c0.1,0.1,0.2,0.2,0.3,0.2C11.7,16,11.9,16,12,16s0.3,0,0.4-0.1c0.1-0.1,0.2-0.1,0.3-0.2l4-4 c0.4-0.4,0.4-1,0-1.4s-1-0.4-1.4,0L13,12.6V5c0-0.6-0.4-1-1-1s-1,0.4-1,1v7.6l-2.3-2.3c-0.4-0.4-1-0.4-1.4,0s-0.4,1,0,1.4 L11.3,15.7z"/><path d="M19,13c-0.6,0-1,0.4-1,1v2c0,1.1-0.9,2-2,2H8c-1.1,0-2-0.9-2-2v-2c0-0.6-0.4-1-1-1s-1,0.4-1,1v2c0,2.2,1.8,4,4,4h8 c2.2,0,4-1.8,4-4v-2C20,13.4,19.6,13,19,13z"/></g></svg>'
		);
		editor.ui.registry.addIcon(
			'openfile','<svg height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M15,3.41421356 L15,7 L18.5857864,7 L15,3.41421356 Z M19,9 L15,9 C13.8954305,9 13,8.1045695 13,7 L13,3 L5,3 L5,21 L19,21 L19,9 Z M5,1 L15.4142136,1 L21,6.58578644 L21,21 C21,22.1045695 20.1045695,23 19,23 L5,23 C3.8954305,23 3,22.1045695 3,21 L3,3 C3,1.8954305 3.8954305,1 5,1 Z M13,13.4142136 L13,18 L11,18 L11,13.4142136 L9.70710678,14.7071068 L8.29289322,13.2928932 L12,9.58578644 L15.7071068,13.2928932 L14.2928932,14.7071068 L13,13.4142136 Z" fill-rule="evenodd"/></svg>'
		);
		editor.on('drop', (e) => process__drop(e));
		editor.on('Dirty', (e) => console.log(e));
		editor.on('Undo', (e) => console.log(editor.isDirty(),e));
	},
	block_unsupported_drop: false,
	save_enablewhendirty: false,
	editimage_cors_hosts: ['picsum.photos'],
	autosave_ask_before_unload: true,
	autosave_interval: '30s',
	autosave_prefix: '{path}{query}-{id}-',
	autosave_restore_when_empty: false,
	autosave_retention: '2m',
	image_advtab: true,
	link_list: [
	{ title: 'My page 1', value: 'https://www.tiny.cloud' },
	{ title: 'My page 2', value: 'http://www.moxiecode.com' }
	],
	image_list: [
	{ title: 'My page 1', value: 'https://www.tiny.cloud' },
	{ title: 'My page 2', value: 'http://www.moxiecode.com' }
	],
	image_class_list: [
	{ title: 'None', value: '' },
	{ title: 'Some class', value: 'class-name' }
	],
	importcss_append: true,
	file_picker_callback: (callback, value, meta) => {
		/* Provide file and text for the link dialog */
		if (meta.filetype === 'file') {
			callback('https://www.google.com/logos/google.jpg', { text: 'My text' });
		}
	
		/* Provide image and alt text for the image dialog */
		if (meta.filetype === 'image') {
			callback('https://www.google.com/logos/google.jpg', { alt: 'My alt text' });
		}
	
		/* Provide alternative source and posted for the media dialog */
		if (meta.filetype === 'media') {
			callback('movie.mp4', { source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg' });
		}
	},
	height: 800,
	image_caption: true,
	quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
	noneditable_class: 'mceNonEditable',
	toolbar_mode: 'sliding',
	contextmenu: 'link image table',
	skin: useDarkMode ? 'oxide-dark' : 'oxide',
	content_css: useDarkMode ? 'dark' : 'default',
	content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
});

const process__drop = (e) => {
	console.log(e.dataTransfer.files);
	e.preventDefault();
	//e.stopPropagation();
	if (e.dataTransfer.items) {
    // Use DataTransferItemList interface to access the file(s)
    [...e.dataTransfer.items].forEach((item, i) => {
      // If dropped items aren't files, reject them
      if (item.kind === "file") {
        const file = item.getAsFile();
        console.log(`…… file[${i}].name = ${file.name}`);
        file.text().then((fdat) => tinymce.activeEditor.execCommand('mceInsertContent', false, fdat));
      }
    });
  } else {
    // Use DataTransfer interface to access the file(s)
    [...e.dataTransfer.files].forEach((file, i) => {
      console.log(`… file[${i}].name = ${file.name}`);
        file.text().then((fdat) => tinymce.activeEditor.execCommand('mceInsertContent', false, fdat));
    });
  }

};

const loadFile = (felm) => {
	const rex = /(.+<body[^>]*>)(.+)(<\/body.+)/s;
	let fr = new FileReader();
	fr.onload = () => {
		let dsegs = fr.result.match(rex);
		if (dsegs && dsegs[2]) {
			d_head = dsegs[1]+"\n";
			d_tail = "\n"+dsegs[3];
			tinymce.activeEditor.setContent(dsegs[2]);
		} else {
			tinymce.activeEditor.setContent(fr.result);
		}
		felm.value = '';
	};
	fr.readAsText(felm.files[0]);
};

const doFileOpen = () => {
	document.getElementById('floader').click();
};
