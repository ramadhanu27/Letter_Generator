# Security Protection Documentation
Indonesian PDF Letter Generator

## Overview
This application implements multiple layers of security protection to prevent unauthorized access to source code and protect against common web vulnerabilities.

## Protection Layers

### 1. JavaScript Protection (`assets/js/security-protection.js`)

#### Right-Click Protection
- Disables context menu on right-click
- Shows security notification when attempted
- Prevents access to "View Page Source" option

#### Keyboard Shortcut Protection
- **F12** - Developer Tools (blocked)
- **Ctrl+Shift+I** - Developer Tools (blocked)
- **Ctrl+U** - View Source (blocked)
- **Ctrl+S** - Save Page (blocked)
- **Ctrl+Shift+C** - Inspect Element (blocked)
- **Ctrl+Shift+J** - Console (blocked)
- **Ctrl+A** - Select All (blocked)
- **Ctrl+P** - Print (blocked)

#### Developer Tools Detection
- Monitors window size changes to detect dev tools
- Shows warning overlay when dev tools are detected
- Threshold: 160px difference in window dimensions
- Logs detection attempts for security monitoring

#### Anti-Debugging Protection
- Uses `debugger` statements to detect debugging attempts
- Monitors execution time to identify debugging
- Shows warning when debugging is detected
- Runs checks every 1000ms

#### Text Selection Protection
- Disables text selection on all elements
- Allows selection only in input fields
- Prevents copy operations on sensitive content

#### Console Protection
- Clears console every 1000ms
- Shows security warnings in console
- Displays anti-XSS warnings
- Logs security events

### 2. CSS Protection (`assets/css/security-protection.css`)

#### User Selection Control
```css
* {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
```

#### Image Protection
- Disables image dragging
- Prevents image context menu
- Blocks image saving attempts

#### Print Protection
- Completely disables printing
- Shows security message when print is attempted
- Hides all content in print media

#### Mobile Protection
- Disables long-press context menu
- Prevents viewport manipulation
- Blocks touch callouts

#### Highlighting Protection
- Makes text selection transparent
- Disables text highlighting
- Prevents content copying

### 3. Server-Side Protection (`.htaccess`)

#### Security Headers
```apache
Header always set X-Frame-Options SAMEORIGIN
Header always set X-Content-Type-Options nosniff
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

#### Content Security Policy
- Restricts script sources to trusted domains
- Prevents inline script execution (with exceptions)
- Controls resource loading

#### File Access Protection
- Blocks direct access to sensitive files (.env, .php, etc.)
- Prevents access to backup files
- Hides version control directories
- Disables directory browsing

#### HTTP Method Protection
- Disables HTTP TRACE method
- Prevents method-based attacks

#### Hotlinking Protection
- Prevents unauthorized resource linking
- Blocks external access to assets

### 4. Application-Level Protection

#### Session Security
- Secure session handling
- CSRF token protection
- Session regeneration on login

#### Input Validation
- Sanitizes all user inputs
- Validates email formats
- Prevents SQL injection

#### Authentication Protection
- Password hashing with bcrypt
- Rate limiting on login attempts
- Account lockout mechanisms

## Implementation Guide

### Adding Protection to New Pages

1. **Include CSS Protection:**
```html
<link href="assets/css/security-protection.css" rel="stylesheet">
```

2. **Include JavaScript Protection:**
```html
<script src="assets/js/security-protection.js"></script>
```

3. **Add Security Classes:**
```html
<div class="no-inspect protected-content">
    <!-- Sensitive content here -->
</div>
```

### Customizing Protection

#### Disable Specific Protections
```javascript
// In security-protection.js, modify config object:
const config = {
    enableRightClickDisable: false,  // Allow right-click
    enableKeyboardDisable: true,     // Keep keyboard protection
    // ... other options
};
```

#### Allow Selection on Specific Elements
```css
.selectable {
    -webkit-user-select: text !important;
    -moz-user-select: text !important;
    user-select: text !important;
}
```

## Testing Security

### Manual Testing
1. Access `/test-security` (admin only)
2. Try right-clicking on the page
3. Attempt keyboard shortcuts (F12, Ctrl+U, etc.)
4. Try selecting text
5. Attempt to drag images
6. Try printing the page

### Automated Testing
The test page includes automated tests for:
- Console access
- Developer tools detection
- Security headers
- Source code access

## Security Considerations

### Limitations
- **Client-side protection only** - Determined users can still bypass
- **JavaScript dependency** - Protection fails if JS is disabled
- **Browser compatibility** - Some features may not work in older browsers

### Best Practices
1. **Defense in depth** - Multiple protection layers
2. **Server-side validation** - Never trust client-side only
3. **Regular updates** - Keep protection methods current
4. **Monitoring** - Log and monitor security events
5. **User education** - Train users on security awareness

### Bypass Methods (for awareness)
- Disabling JavaScript
- Using browser developer tools before page loads
- Viewing page source via browser menu
- Using curl/wget to fetch raw HTML
- Browser extensions that modify page behavior

## Maintenance

### Regular Tasks
1. **Update security headers** - Review and update CSP policies
2. **Monitor logs** - Check for security event patterns
3. **Test protection** - Regularly verify all protections work
4. **Update dependencies** - Keep external libraries current

### Security Monitoring
- Monitor failed login attempts
- Track developer tools detection events
- Log suspicious user behavior
- Review server access logs

## Emergency Procedures

### If Protection is Bypassed
1. **Immediate response** - Block suspicious IP addresses
2. **Investigation** - Analyze logs for attack vectors
3. **Updates** - Patch identified vulnerabilities
4. **Communication** - Notify relevant stakeholders

### Incident Response
1. **Document** - Record all security incidents
2. **Analyze** - Understand attack methods
3. **Improve** - Enhance protection based on findings
4. **Train** - Update security awareness training

## Contact
For security-related questions or to report vulnerabilities, contact the development team.

---
**Note:** This security implementation is designed to deter casual attempts to view source code. Determined attackers with sufficient knowledge can still bypass these protections. Always implement server-side security measures as the primary defense.
