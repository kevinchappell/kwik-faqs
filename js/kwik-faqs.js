jQuery(document).ready(function($) {

	$('.faqs h2 a').on('click', function(e) {
		e.preventDefault();
		var answer = $(this).parents('.faqs:eq(0)').find('.entry-content');
		answer.slideToggle(333, function(){

		});
	});

});
