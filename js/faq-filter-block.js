/**
 * FAQ Filter Block Editor JavaScript
 * Provides the Gutenberg block interface for the FAQ filter
 * 
 * @package KwikFAQs
 * @since 1.0.0
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl, ToggleControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment } = wp.element;

    // Register the FAQ Filter block
    registerBlockType('kwik-faqs/filter', {
        title: __('FAQ Filter', 'kwik'),
        description: __('Add a search filter for FAQ archives', 'kwik'),
        icon: 'search',
        category: 'kwik-faqs',
        keywords: [
            __('faq', 'kwik'),
            __('filter', 'kwik'),
            __('search', 'kwik'),
        ],
        attributes: {
            title: {
                type: 'string',
                default: __('Search FAQs', 'kwik'),
            },
            placeholder: {
                type: 'string',
                default: __('Search FAQs...', 'kwik'),
            },
            showResultsCount: {
                type: 'boolean',
                default: true,
            },
        },
        supports: {
            align: ['left', 'center', 'right', 'wide', 'full'],
            className: true,
            customClassName: true,
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { title, placeholder, showResultsCount } = attributes;

            return el(Fragment, {},
                // Block controls in the sidebar
                el(InspectorControls, {},
                    el(PanelBody, {
                        title: __('FAQ Filter Settings', 'kwik'),
                        initialOpen: true,
                    },
                        el(TextControl, {
                            label: __('Title', 'kwik'),
                            value: title,
                            onChange: function(newTitle) {
                                setAttributes({ title: newTitle });
                            },
                            help: __('Leave empty to hide the title', 'kwik'),
                        }),
                        el(TextControl, {
                            label: __('Placeholder Text', 'kwik'),
                            value: placeholder,
                            onChange: function(newPlaceholder) {
                                setAttributes({ placeholder: newPlaceholder });
                            },
                        }),
                        el(ToggleControl, {
                            label: __('Show Results Count', 'kwik'),
                            checked: showResultsCount,
                            onChange: function(newShowResultsCount) {
                                setAttributes({ showResultsCount: newShowResultsCount });
                            },
                            help: __('Display the number of matching FAQs', 'kwik'),
                        })
                    )
                ),

                // Block preview in the editor
                el('div', {
                    className: 'kwik-faq-filter-block-editor-preview',
                    style: {
                        padding: '20px',
                        border: '2px dashed #ccc',
                        borderRadius: '4px',
                        textAlign: 'center',
                        backgroundColor: '#f9f9f9'
                    }
                },
                    title && el('h3', {
                        style: { 
                            marginTop: '0',
                            marginBottom: '15px',
                            fontSize: '16px',
                            fontWeight: '600'
                        }
                    }, title),
                    
                    el('div', {
                        style: {
                            maxWidth: '300px',
                            margin: '0 auto'
                        }
                    },
                        el('input', {
                            type: 'search',
                            placeholder: placeholder,
                            disabled: true,
                            style: {
                                width: '100%',
                                padding: '10px 15px',
                                fontSize: '14px',
                                border: '2px solid #e0e0e0',
                                borderRadius: '6px',
                                backgroundColor: '#fff'
                            }
                        }),
                        
                        showResultsCount && el('div', {
                            style: {
                                marginTop: '8px',
                                fontSize: '12px',
                                color: '#666',
                                fontStyle: 'italic'
                            }
                        }, __('Results count will appear here', 'kwik'))
                    ),
                    
                    el('p', {
                        style: {
                            marginTop: '15px',
                            marginBottom: '0',
                            fontSize: '12px',
                            color: '#666'
                        }
                    }, __('🔍 FAQ Filter Block - Only visible on FAQ archive pages', 'kwik'))
                )
            );
        },

        save: function() {
            // Return null since this is a dynamic block (rendered server-side)
            return null;
        },
    });

})();
