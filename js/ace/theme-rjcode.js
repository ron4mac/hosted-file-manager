define("ace/theme/rjcode",
	["require","exports","module","ace/lib/dom"],
	function(e,t,n){
		t.isDark=!1,
		t.cssClass="ace-rjcode",
		t.cssText=".ace-rjcode .ace_gutter {background: #e8e8e8;color: #333}\
			.ace-rjcode .ace_print-margin {width: 1px;background: #e8e8e8}\
			.ace-rjcode {background-color: #FFFFFF;color: #000000}\
			.ace-rjcode .ace_cursor {border-left: 2px solid #000000}\
			.ace-rjcode .ace_overwrite-cursors .ace_cursor {border-left: 0px;border-bottom: 1px solid #000000}\
			.ace-rjcode .ace_marker-layer .ace_selection {background: #B5D5FF}\
			.ace-rjcode.ace_multiselect .ace_selection.ace_start {box-shadow: 0 0 3px 0px #FFFFFF;border-radius: 2px}\
			.ace-rjcode .ace_marker-layer .ace_step {background: rgb(198, 219, 174)}\
			.ace-rjcode .ace_marker-layer .ace_bracket {margin: -1px 0 0 -1px;border: 1px solid #BFBFBF;background-color:#FFC3D8}\
			.ace-rjcode .ace_marker-layer .ace_active-line {background: rgba(0, 0, 0, 0.071)}\
			.ace-rjcode .ace_gutter-active-line {background-color: rgba(0, 0, 0, 0.071)}\
			.ace-rjcode .ace_marker-layer .ace_selected-word {border: 1px solid #B5D5FF}\
			.ace-rjcode .ace_constant.ace_language,.ace-rjcode .ace_keyword,.ace-rjcode .ace_meta,.ace-rjcode .ace_variable.ace_language {color: #C800A4}\
			.ace-rjcode .ace_invisible {color: #BFBFBF}\
			.ace-rjcode .ace_constant.ace_character,.ace-rjcode .ace_constant.ace_other {color: #275A5E}\
			.ace-rjcode .ace_constant.ace_numeric {color: #3A00DC}\
			.ace-rjcode .ace_entity.ace_other.ace_attribute-name,.ace-rjcode .ace_support.ace_constant,.ace-rjcode .ace_support.ace_function {color: #450084}\
			.ace-rjcode .ace_fold {background-color: #C800A4;border-color: #000000}\
			.ace-rjcode .ace_entity.ace_name.ace_tag,.ace-rjcode .ace_support.ace_class,.ace-rjcode .ace_support.ace_type {color: #790EAD}\
			.ace-rjcode .ace_storage {color: #C900A4}\
			.ace-rjcode .ace_string {color: #DF0002}\
			.ace-rjcode .ace_comment {color: #008E00}\
			.ace-rjcode .ace_indent-guide {background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAE0lEQVQImWP4////f4bLly//BwAmVgd1/w11/gAAAABJRU5ErkJggg==) right repeat-y}";
		var r=e("../lib/dom");
		r.importCssString(t.cssText,t.cssClass);
	});