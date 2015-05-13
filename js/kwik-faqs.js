jQuery(document).ready(function($) {

	$('.faqs h2 a').on('click', function(e) {
		e.preventDefault();
		var $answer = $(this).parents('.faqs').find('.entry-content');
		console.debug($answer);
		$answer.slideToggle(333, function(){
			console.log('toggled');
		});
	});

});
