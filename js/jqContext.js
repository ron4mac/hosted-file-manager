(function($) {

	var menu, trigger, content, hash, currentTarget;
	var defaults = {
		menuStyle: {
//			listStyle: 'none',
//			padding: '1px',
//			margin: '0px',
//			backgroundColor: '#fff',
//			border: '1px solid #999',
//			width: '100px',
//			'box-shadow': '2px 2px 8px #BBB'
		},
		itemStyle: {
//			margin: '0px',
//			color: '#000',
//			display: 'block',
//			cursor: 'default',
//			padding: '3px',
//			border: '1px solid #fff',
//			backgroundColor: 'transparent'
		},
		hoverStyle: {
//			border: '1px solid #0a246a',
//			backgroundColor: '#b6bdd2'
		},
		eventPosX: 'pageX',
		eventPosY: 'pageY',
		onContextMenu: null,
		onShowMenu: null
	};

	$.fn.contextMenu = function(id, options) {
		if (!menu) {
			// Create singleton menu
			menu = $('<div id="jqContextMenu" class="jqContextMenu"></div>')
				.hide()
				.css({position:'absolute', zIndex:'500'})
				.appendTo('body')
				.bind('click', function(e) {
					e.stopPropagation();
				});
		}
		hash = hash || [];
		hash.push({
			id: id,
			menuStyle: $.extend({}, defaults.menuStyle, options.menuStyle || {}),
			itemStyle: $.extend({}, defaults.itemStyle, options.itemStyle || {}),
			hoverStyle: $.extend({}, defaults.hoverStyle, options.hoverStyle || {}),
			bindings: options.bindings || {},
			onContextMenu: options.onContextMenu || defaults.onContextMenu,
			onShowMenu: options.onShowMenu || defaults.onShowMenu,
			eventPosX: options.eventPosX || defaults.eventPosX,
			eventPosY: options.eventPosY || defaults.eventPosY
		});

		var index = hash.length - 1;
		$(this).bind('contextmenu', function(e) {
			// Check if onContextMenu() defined
			var bShowContext = (!!hash[index].onContextMenu) ? hash[index].onContextMenu(e) : true;
			if (bShowContext) display(index, this, e, options);
			return false;
		});
		return this;
	};

	function display(index, trigger, e, options) {
		var cur = hash[index];
		content = $('#'+cur.id).find('ul:first').clone(true);
		content.css(cur.menuStyle).find('li').css(cur.itemStyle).hover(
			function() {
				$(this).css(cur.hoverStyle);
			},
			function() {
				$(this).css(cur.itemStyle);
			}
			).find('img').css({verticalAlign:'middle',paddingRight:'2px'});

		// Send the content to the menu
		menu.html(content);

		// if there's an onShowMenu, run it now -- must run after content has been added
		// if you try to alter the content variable before the menu.html(), IE6 has issues
		// updating the content
		if (!!cur.onShowMenu) menu = cur.onShowMenu(e, menu);

		$.each(cur.bindings, function(id, func) {
			$('#'+id, menu).bind('click', function(e) {
				hide();
				func(trigger, currentTarget);
				});
		});

		menu.css({'left':e[cur.eventPosX],'top':e[cur.eventPosY]}).show();
		$(document).one('click', hide);
	}

	function hide() {
		menu.hide();
	}

	// Apply defaults
	$.contextMenu = {
		defaults: function(userDefaults) {
			$.each(userDefaults, function(i, val) {
				if (typeof val == 'object' && defaults[i]) {
					$.extend(defaults[i], val);
				}
				else defaults[i] = val;
			});
		}
	};

})(jQuery);

//$(function() {
//	$('div.contextMenu').hide();
//});