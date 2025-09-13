jQuery(document).ready(function ($) {

  function initFAQAccordion() {

    // FAQ Toggle functionality
    $(document).on('click', '.faq-toggle', function (e) {
      e.preventDefault();
      const $this = $(this);
      const $faqItem = $this.closest('.faq-item');
      const $answer = $faqItem.find('.faq-answer');

      // Close other answers (accordion behavior)
      $('.faq-answer').not($answer).slideUp(300, function () {
        $(this).closest('.faq-item').removeClass('faq-active');
      });

      // Toggle current answer
      $answer.slideToggle(300, function () {
        // Toggle active state on question
        $faqItem.toggleClass('faq-active', $answer.is(':visible'));
      });
    });
  }


  initFAQAccordion();


});
