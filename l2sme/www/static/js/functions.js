
var Slider = {
	self: this, 
	init: function (el, options) {
		var slider = $.extend({
			self: $(el), 
			max: 255, 
			min: 0, 
			value: null, 
			onmove: null, 
			onchange: null
		}, options);
		
		if (slider.value === null)
			slider.value = slider.min;
		
		slider.range = $('<div class="slider_range">&nbsp;</div>');
		slider.pointer = $('<button class="slider_pointer"></button>');
		
		slider.self.on("mousedown.ololo_slider", null, slider, Slider.startMove);
		
		slider.self.append(slider.range);
		slider.self.append(slider.pointer);
		
		slider.self.addClass('slider_container');
		slider.self.prop("slider", slider);
		
		slider.self[0].sliderSetValue = Slider._set;
		slider.self[0].sliderGetValue = Slider._get;
		
		slider.self[0].sliderSetValue(slider.value);
	}, 
	startMove: function (e) {
		var slider = e.data;
		$(document).on("mouseup.ololo_slider", null, slider, Slider.stopMove);
		$(document).on("mousemove.ololo_slider", null, slider, Slider.move);
		
		Slider.move(e);
	}, 
	stopMove: function (e) {
		var slider = e.data;
		$(this).off("mouseup.ololo_slider");
		$(this).off('mousemove.ololo_slider');
	}, 
	move: function (e) {
		e.stopPropagation();
		
		var slider = e.data;
		var pos = slider.self.offset();
		var end_x = pos.left + slider.self.width();
		
		var x = 0;
		if (pos.left >= e.pageX) {
			x = 0;
		} else if (end_x <= e.pageX) {
			x = end_x - pos.left;
		} else
			x = e.pageX - pos.left;
		
		Slider.updateValue(slider, (100 * x / slider.self.width()));
	}, 
	updateValue: function(slider, p) {
		if (slider.value < slider.min)
			slider.value = slider.min;
		if (slider.value > slider.max)
			slider.value = slider.max;
		
		if (p !== null && p !== undefined)
			slider.value = slider.min + Math.ceil((slider.max - slider.min) / 100 * p);
		else
			p = slider.value * 100 / slider.max;
		
		slider.range.css('width', p + '%');
		slider.pointer.css('left', p + '%');
		
		if (slider.onmove)
			slider.onmove(slider.value);
	}, 
	_set: function (value) {
		this.slider.value = value;
		Slider.updateValue(this.slider, null);
		return this;
	}, 
	_get: function (value) {
		return this.slider.value;
	}
};
function round(number, precision) {
	var multiplier = Math.pow(10, precision);
	return (Math.round(number * multiplier) / multiplier);
}
