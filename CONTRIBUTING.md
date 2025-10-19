# Contributing to Kwik FAQs

Thank you for your interest in contributing to Kwik FAQs! This document provides guidelines and instructions for contributing to the project.

## Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help create a welcoming environment for all contributors

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title**: Descriptive summary of the issue
- **Steps to reproduce**: Detailed steps to reproduce the behavior
- **Expected behavior**: What you expected to happen
- **Actual behavior**: What actually happened
- **Environment**: WordPress version, PHP version, plugin version
- **Screenshots**: If applicable

### Suggesting Enhancements

Enhancement suggestions are welcome! Please include:

- **Clear description**: Detailed explanation of the feature
- **Use case**: Why this feature would be useful
- **Possible implementation**: Any ideas on how to implement it
- **Alternatives considered**: Other approaches you've considered

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Follow WordPress coding standards**:
   - Use WordPress PHP Coding Standards
   - Follow WordPress JavaScript Coding Standards
   - Use tabs for indentation (not spaces)
   - Add PHPDoc blocks for functions and classes

3. **Make your changes**:
   - Write clear, concise commit messages
   - Include comments for complex logic
   - Update documentation as needed
   - Test your changes thoroughly

4. **Test your code**:
   - Test with latest WordPress version
   - Test with minimum required WordPress version (5.0)
   - Test with PHP 7.4 and PHP 8.x
   - Ensure no PHP errors or warnings
   - Test frontend and admin functionality

5. **Submit the pull request**:
   - Provide clear description of changes
   - Reference any related issues
   - Include before/after screenshots if applicable

## Development Setup

### Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Git

### Local Development

```bash
# Clone the repository
git clone https://github.com/yourusername/kwik-faqs.git

# Install in WordPress plugins directory
cp -r kwik-faqs /path/to/wordpress/wp-content/plugins/

# Activate the plugin in WordPress admin
```

## Coding Standards

### PHP

- Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Use type hints for PHP 7.4+ (parameters and return types)
- Use strict comparison (`===` instead of `==`)
- Escape all output (`esc_html()`, `esc_attr()`, `esc_url()`)
- Sanitize all input (`sanitize_text_field()`, `wp_kses_post()`)
- Validate user capabilities and nonces

### JavaScript

- Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- Use modern JavaScript (ES6+)
- Use jQuery only when necessary
- Comment complex logic

### CSS

- Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
- Use descriptive class names
- Avoid `!important` unless absolutely necessary
- Consider accessibility

## Security Guidelines

- Never trust user input
- Always validate and sanitize data
- Use WordPress nonces for form submissions
- Check user capabilities before performing actions
- Use prepared statements for database queries
- Escape all output to prevent XSS
- Report security issues privately (see SECURITY.md)

## File Structure

```
kwik-faqs/
├── css/              # Stylesheets
├── docs/             # Documentation
├── inc/              # PHP classes
├── js/               # JavaScript files
├── template/         # Template files
├── widgets/          # Widget classes
├── kwik-faqs.php     # Main plugin file
├── README.md         # Plugin documentation
├── LICENSE           # License information
├── SECURITY.md       # Security policy
└── CONTRIBUTING.md   # This file
```

## Documentation

- Update README.md for user-facing changes
- Update inline documentation for code changes
- Add examples for new features
- Keep documentation clear and concise

## Testing Checklist

Before submitting a pull request, verify:

- [ ] Code follows WordPress coding standards
- [ ] No PHP errors or warnings
- [ ] Works with latest WordPress version
- [ ] Works with minimum WordPress version (5.0)
- [ ] Tested with PHP 7.4 and PHP 8.x
- [ ] All functions have proper documentation
- [ ] Security best practices followed
- [ ] No sensitive data in code
- [ ] Forms have nonce verification
- [ ] User input is sanitized
- [ ] Output is properly escaped

## Questions?

If you have questions about contributing, feel free to:

- Open an issue for discussion
- Check existing documentation
- Review closed issues for similar questions

## License

By contributing to Kwik FAQs, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Kwik FAQs! 🎉
