jQuery(document).ready(function($) {

	$('.faq-toggle').on('click', function(e) {
		e.preventDefault();
		var $this = $(this);
		var $answer = $this.closest('.faq-item').find('.faq-answer');

		// Close other answers
		$('.faq-answer').not($answer).slideUp(333);

		// Toggle current answer
		$answer.slideToggle(333);
	});

});
