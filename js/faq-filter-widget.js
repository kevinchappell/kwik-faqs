/**
 * FAQ Filter Widget JavaScript
 * Provides real-time search and filter functionality for FAQ archives via sidebar widget
 * 
 * @package KwikFAQs
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initFAQFilterWidget();
        // initFAQAccordion();
    });

    /**
     * Initialize FAQ filter widget functionality
     */
    function initFAQFilterWidget() {
        const $searchInput = $('.faq-search-widget');
        const $faqItems = $('.faq-item');
        const $resultsCount = $('.faq-widget-results-count');
        const $noResults = $('#faq-no-results');
        let searchTimeout;

        if (!$searchInput.length || !$faqItems.length) return;

        // Initial count
        updateResultsCount($faqItems.length, $faqItems.length);

        // Handle search input with debouncing
        $searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val().toLowerCase().trim();

            searchTimeout = setTimeout(function() {
                filterFAQs(searchTerm);
            }, 300); // 300ms debounce
        });

        // Clear search on escape key
        $searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('');
                filterFAQs('');
            }
        });

        /**
         * Filter FAQ items based on search term
         * @param {string} searchTerm - The search term to filter by
         */
        function filterFAQs(searchTerm) {
            let visibleCount = 0;
            const totalCount = $faqItems.length;

            $faqItems.each(function() {
                const $item = $(this);
                const $title = $item.find('.faq-question button, .faq-question a');
                const $content = $item.find('.faq-answer');
                
                // Get text content from DOM elements
                const title = $title.text().toLowerCase();
                const content = $content.text().toLowerCase();
                
                // Check if search term matches title or content
                const isMatch = searchTerm === '' || 
                               title.includes(searchTerm) || 
                               content.includes(searchTerm);

                if (isMatch) {
                    $item.show().addClass('faq-visible');
                    visibleCount++;
                } else {
                    $item.hide().removeClass('faq-visible');
                }
            });

            // Update results count and show/hide no results message
            updateResultsCount(visibleCount, totalCount);
            
            // Show/hide no results message if it exists
            if ($noResults.length) {
                if (visibleCount === 0 && searchTerm !== '') {
                    $noResults.show();
                } else {
                    $noResults.hide();
                }
            }

            // Highlight search terms in visible items
            if (searchTerm !== '') {
                highlightSearchTerms(searchTerm);
            } else {
                removeHighlights();
            }

            // Trigger custom event for other scripts to listen to
            $(document).trigger('faqFiltered', {
                searchTerm: searchTerm,
                visibleCount: visibleCount,
                totalCount: totalCount
            });
        }

        /**
         * Update the results count display
         * @param {number} visible - Number of visible items
         * @param {number} total - Total number of items
         */
        function updateResultsCount(visible, total) {
            if (!$resultsCount.length) return;
            
            let countText = '';
            
            if (visible === total) {
                countText = `Showing all ${total} FAQs`;
            } else {
                countText = `Showing ${visible} of ${total} FAQs`;
            }
            
            $resultsCount.text(countText);
        }

        /**
         * Highlight search terms in FAQ titles and content
         * @param {string} searchTerm - The term to highlight
         */
        function highlightSearchTerms(searchTerm) {
            $('.faq-visible').each(function() {
                const $item = $(this);
                const $title = $item.find('.faq-question button, .faq-question a');
                const $content = $item.find('.faq-answer');

                // Highlight in title
                highlightText($title, searchTerm);
                
                // Highlight in content
                highlightText($content, searchTerm);
            });
        }

        /**
         * Highlight specific text within an element
         * @param {jQuery} $element - Element to highlight text in
         * @param {string} searchTerm - Term to highlight
         */
        function highlightText($element, searchTerm) {
            if (!$element.length || !searchTerm) return;

            const originalText = $element.data('original-text') || $element.html();
            if (!$element.data('original-text')) {
                $element.data('original-text', originalText);
            }

            const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
            const highlightedText = originalText.replace(regex, '<mark class="faq-highlight">$1</mark>');
            
            $element.html(highlightedText);
        }

        /**
         * Remove all highlights from FAQ items
         */
        function removeHighlights() {
            $('.faq-item').each(function() {
                const $item = $(this);
                const $title = $item.find('.faq-question button, .faq-question a');
                const $content = $item.find('.faq-answer');

                // Restore original text
                if ($title.data('original-text')) {
                    $title.html($title.data('original-text'));
                }
                if ($content.data('original-text')) {
                    $content.html($content.data('original-text'));
                }
            });
        }

        /**
         * Escape special regex characters
         * @param {string} string - String to escape
         * @returns {string} Escaped string
         */
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
    }

    /**
     * Initialize FAQ accordion functionality (compatible with both theme and plugin templates)
     */
    function initFAQAccordion() {
        // Handle both button-based toggles (theme) and link-based toggles (plugin)
        const $faqToggles = $('.faq-toggle');

        $faqToggles.on('click', function(e) {
            e.preventDefault();
            
            const $toggle = $(this);
            const $faqItem = $toggle.closest('.faq-item');
            const $answer = $faqItem.find('.faq-answer');
            const $icon = $toggle.find('.faq-toggle-icon');
            
            // Handle button-based toggles (theme template)
            if ($toggle.is('button')) {
                const isExpanded = $toggle.attr('aria-expanded') === 'true';

                if (isExpanded) {
                    // Collapse
                    $answer.slideUp(300, function() {
                        $answer.attr('aria-hidden', 'true');
                    });
                    $toggle.attr('aria-expanded', 'false');
                    if ($icon.length) $icon.text('+');
                    $toggle.removeClass('expanded');
                } else {
                    // Expand
                    $answer.slideDown(300, function() {
                        $answer.attr('aria-hidden', 'false');
                    });
                    $toggle.attr('aria-expanded', 'true');
                    if ($icon.length) $icon.text('−');
                    $toggle.addClass('expanded');
                }
            } else {
                // Handle link-based toggles (plugin template)
                $answer.slideToggle(300);
                $toggle.toggleClass('expanded');
            }
        });

        // Handle deep linking to specific FAQs
        if (window.location.hash && window.location.hash.startsWith('#faq-')) {
            const $targetFAQ = $(window.location.hash);
            if ($targetFAQ.length) {
                const $toggle = $targetFAQ.find('.faq-toggle');
                $toggle.trigger('click');
                
                // Scroll to the FAQ after a short delay
                setTimeout(function() {
                    $targetFAQ[0].scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }, 350);
            }
        }
    }

})(jQuery);
