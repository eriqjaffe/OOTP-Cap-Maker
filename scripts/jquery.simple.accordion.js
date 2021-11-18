/*
* Accordion v1.0
* Developed by Yuriy Vaskevich
* Simple jQuery Accordion plugin
* Date: 02/24/2013
* Usege: $("dl").SimleAccordion();
* This work is licensed under a Creative Commons Attribution-ShareAlike 3.0
* Unported License: http://creativecommons.org/licenses/by-sa/3.0/
*/
(function($) {
	$.fn.SimpleAccordion = function () {
		var accordion = $(this); // Cache element
		accordion.hide().fadeIn(); // Fade in on load
		accordion.find(".active").show(); // Open active panel
		accordion.find("dt").on("click", function () { // Listen to onClick
			var current = $(this).next("dd"); // Cache current
			if (current.is(":hidden")) { // Check if not active
				current.slideDown().siblings("dd").slideUp(); // Open curren panel
			}
		});
	};
})(jQuery);