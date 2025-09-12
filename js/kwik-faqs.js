jQuery(document).ready(function($) {

	// FAQ Toggle functionality
	$(document).on('click', '.faq-toggle', function(e) {
		e.preventDefault();
		var $this = $(this);
		var $faqItem = $this.closest('.faq-item');
		var $answer = $faqItem.find('.faq-answer');

		// Close other answers (accordion behavior)
		$('.faq-answer').not($answer).slideUp(300);

		// Toggle current answer
		$answer.slideToggle(300, function() {
			// Toggle active state on question
			$faqItem.toggleClass('faq-active', $answer.is(':visible'));
		});
	});

});
