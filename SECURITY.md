# Security Policy

## Supported Versions

Security updates are provided for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.2.x   | :white_check_mark: |
| 1.1.x   | :white_check_mark: |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take the security of Kwik FAQs seriously. If you discover a security vulnerability, please follow these steps:

### How to Report

1. **Do NOT** create a public GitHub issue for security vulnerabilities
2. Email the maintainer directly at the email address listed in the plugin author information
3. Include the following information in your report:
   - Description of the vulnerability
   - Steps to reproduce the issue
   - Potential impact
   - Suggested fix (if you have one)

### What to Expect

- **Initial Response**: You should receive an acknowledgment within 48 hours
- **Status Updates**: We'll keep you informed about the progress of fixing the vulnerability
- **Disclosure Timeline**: We aim to address critical issues within 7-14 days
- **Credit**: If you'd like, we'll acknowledge your contribution in the security advisory

### Security Best Practices

When using this plugin, we recommend:

- Keep WordPress, PHP, and all plugins updated to the latest stable versions
- Use strong passwords and proper user role management
- Regularly backup your WordPress installation
- Monitor your site for suspicious activity
- Follow WordPress security best practices

## Security Features

Kwik FAQs implements the following security measures:

- **Input Validation**: All user input is validated and sanitized
- **Nonce Verification**: All forms use WordPress nonces for CSRF protection
- **Capability Checks**: Admin functions require proper user capabilities
- **Prepared Statements**: Database queries use prepared statements
- **Output Escaping**: All output is properly escaped to prevent XSS
- **File Upload Validation**: Import functionality validates file types and content
- **No Hardcoded Credentials**: No API keys, tokens, or sensitive data in code

## Responsible Disclosure

We follow responsible disclosure practices:

1. Report is received and acknowledged
2. Vulnerability is verified and assessed
3. Fix is developed and tested
4. Update is released
5. Security advisory is published (if applicable)
6. Reporter is credited (if desired)

Thank you for helping keep Kwik FAQs and its users safe!
