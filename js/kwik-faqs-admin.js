jQuery(document).ready(function($) {

  $('#sortable-table tbody').sortable({
    axis: 'y',
    handle: '.column-order img',
    placeholder: 'ui-state-highlight',
    forcePlaceholderSize: true,
    update: function(event, ui) {
      var theOrder = $(this).sortable('toArray');
      var nonce = $(this).attr('data-nonce');
      var $tbody = $(this);

      var data = {
        action: 'faqs_update_post_order',
        postType: $(this).attr('data-post-type'),
        order: theOrder,
        nonce: nonce
      };

      // Show loading state
      $tbody.addClass('updating');
      
      $.post(ajaxurl, data)
        .done(function(response) {
          // Success - remove loading state
          $tbody.removeClass('updating');
          console.log('FAQ order updated successfully');
        })
        .fail(function(xhr, status, error) {
          // Error handling - remove loading state and show error
          $tbody.removeClass('updating');
          alert('Error updating FAQ order: ' + error);
          console.error('AJAX Error:', xhr.responseText);
        })
        .always(function() {
          // Ensure loading state is always removed
          $tbody.removeClass('updating');
        });
    }
  }).disableSelection();

  // Add some CSS for loading state
  $('<style>')
    .prop('type', 'text/css')
    .html(`
      .updating {
        opacity: 0.6;
        pointer-events: none;
      }
      .updating .move-handle {
        cursor: not-allowed !important;
      }
    `)
    .appendTo('head');

});
