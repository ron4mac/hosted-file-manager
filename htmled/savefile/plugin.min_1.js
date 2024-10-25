const plg__sf__dlog = {
	title: 'Save File ...', // The dialog's title - displayed in the dialog header
	body: {
		type: 'panel', // The root body type - a Panel or TabPanel
		items: [ // A list of panel components
			{
				type: 'input', // component type
				name: 'asFile', // identifier
				inputMode: 'text',
				label: 'As filename ...', // text for the label
				placeholder: '', // placeholder text for the input
				enabled: true, // disabled state
				maximized: false // grow width to take as much space as possible
			},
			{
				type: 'checkbox', // component type
				name: 'down', // identifier
				label: 'Download only', // text for the label
				enabled: true // enabled state
			}
		]
	},
	doDnld: (fn) => {
		let anch = document.createElement('a');
		anch.download = fn;
		anch.type = 'text/html';
		let data = new Blob([tinymce.activeEditor.getContent()]);
		anch.href = URL.createObjectURL(data);
		anch.click();
	},
	onSubmit: (api) => {
		const data = api.getData();
		const fnam = data.asFile;
		const down = data.down;
		if (fnam) {
			if (down) {
				plg__sf__dlog.doDnld(fnam);
			} else {
				document.getElementById('the_fnam').value = fnam;
				tinymce.activeEditor.execCommand('mceSave');
			}
			api.close();
		} else {
			alert('Please specify a file name');
		}
	},
	buttons: [ // A list of footer buttons
		{
			type: 'cancel',
			name: 'cancelButton',
			text: 'Cancel'
		},
		{
			type: 'submit',
			name: 'submitButton',
			text: 'Save',
			buttonType: 'primary',
		}
	]
};

!function() {
	'use strict';
	var plgm = tinymce.util.Tools.resolve('tinymce.PluginManager');
	const askFileSave = () => tinymce.activeEditor.windowManager.open(plg__sf__dlog);
	plgm.add('savefile', (edt => {
		edt.ui.registry.addMenuItem('savefile', {
			icon: 'savefile',
			text: 'Save File',
			onAction: () => askFileSave()
		});
	}));
}();
