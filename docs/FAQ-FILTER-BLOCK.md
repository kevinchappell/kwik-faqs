# FAQ Filter Block Instructions

## Overview
The FAQ Filter Block is a modern Gutenberg block that provides search/filter functionality for FAQ archives. It replaces the traditional widget system with a more flexible block-based approach.

## How to Use

### Adding the Block

1. **In the Block Editor:**
   - Click the "+" button to add a new block
   - Search for "FAQ Filter" or look in the "FAQ Tools" category
   - Click to add the block

2. **In Widget Areas (Appearance > Editor > Templates > Parts):**
   - Edit your sidebar template part
   - Add the FAQ Filter block where you want it
   - Configure the settings as needed

3. **In Content:**
   - The block can be added to any post or page
   - It will only display on FAQ archive pages (hidden elsewhere)

### Block Settings

The block has several customizable options available in the sidebar:

- **Title**: The heading displayed above the filter (leave empty to hide)
- **Placeholder Text**: The text shown in the search input field
- **Show Results Count**: Toggle to show/hide the "X of Y FAQs" counter

### Block Alignment

The block supports WordPress alignment options:
- **Left/Right**: Floats the block to the side (max 300px width)
- **Center**: Centers the block (max 400px width) 
- **Wide**: Wider centered block (max 800px width)
- **Full Width**: Takes up the full container width

## Features

- ✅ **Real-time Search**: Filters FAQs as you type
- ✅ **Content Matching**: Searches both FAQ titles and content
- ✅ **Search Highlighting**: Highlights matching terms in yellow
- ✅ **Results Counter**: Shows number of matching FAQs
- ✅ **Keyboard Support**: ESC key clears search
- ✅ **Accessible**: Proper ARIA labels and screen reader support
- ✅ **Mobile Responsive**: Works on all device sizes
- ✅ **Performance**: 300ms debounced input for smooth performance

## Compatibility

- **WordPress 5.8+**: Full block editor support
- **Classic Widgets**: Also works with the traditional widget system
- **Theme Integration**: Uses existing FAQ archive templates
- **Plugin Integration**: Part of the Kwik FAQs plugin

## Technical Notes

- The block only displays on FAQ archive pages (`is_post_type_archive('faqs')`)
- Scripts and styles are only loaded when needed
- Compatible with both the theme's enhanced FAQ template and the plugin's default template
- Uses jQuery for DOM manipulation and event handling

## Troubleshooting

**Block not visible:**
- Make sure you're on a FAQ archive page
- Check that the Kwik FAQs plugin is active
- Verify that FAQs are published and visible

**Search not working:**
- Check browser console for JavaScript errors
- Ensure jQuery is loaded
- Verify that FAQ items have the correct CSS classes (`faq-item`, `faq-question`, `faq-answer`)

**Styling issues:**
- The block inherits styles from your theme
- Custom CSS can be added to your theme's stylesheet
- Block-specific classes: `.kwik-faq-filter-block`, `.faq-filter-widget-container`
