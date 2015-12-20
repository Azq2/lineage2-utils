
function to_hex(i, n) {
	var s = '';
	for (var j = 0; j < n; ++j)
		s += '0';
	s += i.toString(16);
	return s.substr(-n);
}

function parse_color(color) {
	var hex_color = '000', m;
	if ((m = color.match(/\((\d+), (\d+), (\d+)\)/i))) {
		var r = m[1], g = m[2], b = m[3];
		hex_color = to_hex((r << 16) | (g << 8) | b, 6);
	}
	return hex_color;
}

(function ($) {
//
var ns = '.OloloSlider' + Date.now();
var Slider = function (el, opts) {
	var self = this, 
		current_val, 
		range = $('<div class="slider_range">&nbsp;</div>'), 
		pointer = $('<button class="slider_pointer"></button>');
	
	self._setup = function () {
		var start_x, max_width, base_x;
		var on_move = function (e) {
			var pct = Math.max(Math.min(e.pageX - base_x, max_width), 0) / max_width, 
				value = opts.min + (opts.max - opts.min) * pct;
			self.value(value);
		};
		el.on('mousedown', function (e) {
			if (e.which && e.which != 1)
				return;
			
			base_x = el.offset().left;
			start_x = e.pageX;
			max_width = el.innerWidth();
			
			on_move(e);
			$(document).on('mousemove' + ns, on_move).on('mouseup' + ns, function (e) {
				$(document).off(ns);
			});
		});
		el.append(range).append(pointer).addClass('slider_container');
		
		self.value(opts.min);
	};
	self.value = function (value) {
		if (value === undefined || value === null || value === false) {
			return current_val;
		} else {
			current_val = Math.min(Math.max(opts.min, value), opts.max);
			
			var pct = ((current_val - opts.min) / (opts.max - opts.min)).toFixed(2) * 100;
			range.css('width', pct + '%');
			pointer.css('left', pct + '%');
			
			var evt = $.Event('sliderUpdate');
			evt.value = current_val;
			el.trigger(evt);
		}
		return self;
	};
	
	self._setup();
};

$.fn.slider = function (opts) {
	var el = this.first();
	if (el.length) {
		var slider = el.data('OloloSlider');
		if (!slider)
			slider = new Slider(el, opts);
		el.data('OloloSlider', slider);
		return slider;
	}
	return null;
};
//
})(jQuery);

$(function () {
	var is_fixed = false, 
		nav = $('#up_to_page'), 
		$window = $(window);
	
	$('#up_to_page').click(function (e) {
		$('body').animate({scrollTop: 0}, 300);
	});
	
	$window.scroll(function () {
		if ($window.scrollTop() > 0) {
			if (!is_fixed) {
				nav.show();
				is_fixed = true;
			}
		} else {
			if (is_fixed) {
				nav.hide();
				is_fixed = false;
			}
		}
	});
	
	$('body').on('click', '.tab', function (e) {
		e.preventDefault();
		var el = $(this), parent = el.parents('.tabs'), 
			target_id = el.data('content');
		parent.find('.tab.active').removeClass('active');
		parent.find('.tab-content.active').removeClass('active');
		el.addClass('active');
		
		parent.find('.tab-content').each(function () {
			var tab_content = $(this);
			if (tab_content.data('id') == target_id) {
				tab_content.addClass('active');
				return false;
			}
		});
	});
});