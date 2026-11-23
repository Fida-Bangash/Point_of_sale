{{-- Keeps Select2 dropdowns in the right place when the page is zoomed with the app's own zoom
     (CSS "zoom" on <html>, see partials/zoom.blade.php). Select2 works out the position in screen
     pixels but the zoomed page draws it again at that size, so the list lands in the wrong spot.
     Include this AFTER select2.min.js and BEFORE assets/js/script.js. At 100% zoom it does nothing. --}}
<script>
(function ($) {
	if (!$ || !$.fn || !$.fn.select2 || !$.fn.select2.amd) { return; }

	var AttachBody;
	try { AttachBody = $.fn.select2.amd.require('select2/dropdown/attachBody'); } catch (e) { return; }
	if (!AttachBody || !AttachBody.prototype) { return; }

	var originalPosition = AttachBody.prototype._positionDropdown;
	var originalResize = AttachBody.prototype._resizeDropdown;

	// Current page zoom (1 = 100%)
	function pageZoom() {
		var z = parseFloat(window.getComputedStyle(document.documentElement).zoom);
		return (isFinite(z) && z > 0 && Math.abs(z - 1) > 0.001) ? z : 1;
	}

	AttachBody.prototype._positionDropdown = function () {
		var z = pageZoom();

		if (z === 1) {
			this.$dropdownContainer.css('position', 'absolute');
			return originalPosition.apply(this, arguments);
		}

		var dropdown = this.$dropdown[0];
		var isAbove = dropdown.classList.contains('select2-dropdown--above');
		var isBelow = dropdown.classList.contains('select2-dropdown--below');

		// Everything below is in screen pixels; only the final CSS values are divided by the zoom
		var box = this.$container[0].getBoundingClientRect();
		var height = dropdown.getBoundingClientRect().height;

		var roomBelow = window.innerHeight > box.bottom + height;
		var roomAbove = box.top > height;

		var direction = null;
		if (!isAbove && !isBelow) { direction = 'below'; }
		if (!roomBelow && roomAbove && !isAbove) { direction = 'above'; }
		else if (!roomAbove && roomBelow && isAbove) { direction = 'below'; }

		var opensAbove = direction === 'above' || (isAbove && direction !== 'below');
		var top = opensAbove ? box.top - height : box.bottom;

		if (direction !== null) {
			dropdown.classList.remove('select2-dropdown--below', 'select2-dropdown--above');
			dropdown.classList.add('select2-dropdown--' + direction);
			this.$container[0].classList.remove('select2-container--below', 'select2-container--above');
			this.$container[0].classList.add('select2-container--' + direction);
		}

		// Fixed = relative to the window, so page scrolling does not matter
		this.$dropdownContainer.css({
			position: 'fixed',
			left: (box.left / z) + 'px',
			top: (top / z) + 'px'
		});
	};

	AttachBody.prototype._resizeDropdown = function () {
		var z = pageZoom();

		if (z === 1) {
			return originalResize.apply(this, arguments);
		}

		var css = { width: (this.$container[0].getBoundingClientRect().width / z) + 'px' };

		if (this.options.get('dropdownAutoWidth')) {
			css.minWidth = css.width;
			css.position = 'relative';
			css.width = 'auto';
		}

		this.$dropdown.css(css);
	};
})(window.jQuery);
</script>