/**
 * Security Protection Script
 * Indonesian PDF Letter Generator
 * 
 * This script provides multiple layers of protection against:
 * - View source attempts
 * - Developer tools access
 * - Right-click context menu
 * - Text selection and copying
 * - Printing and saving
 * - Debugging attempts
 */

(function() {
    'use strict';

    // Configuration
    const config = {
        enableRightClickDisable: true,
        enableKeyboardDisable: true,
        enableDevToolsDetection: true,
        enableTextSelectionDisable: true,
        enableConsoleWarning: true,
        enableAntiDebugging: true,
        enablePrintDisable: true,
        devToolsThreshold: 160,
        debuggerCheckInterval: 1000,
        consoleCheckInterval: 1000
    };

    // Initialize protection when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeProtection);
    } else {
        initializeProtection();
    }

    function initializeProtection() {
        if (config.enableRightClickDisable) {
            disableRightClick();
        }
        
        if (config.enableKeyboardDisable) {
            disableKeyboardShortcuts();
        }
        
        if (config.enableDevToolsDetection) {
            detectDevTools();
        }
        
        if (config.enableTextSelectionDisable) {
            disableTextSelection();
        }
        
        if (config.enableConsoleWarning) {
            showConsoleWarning();
        }
        
        if (config.enableAntiDebugging) {
            enableAntiDebugging();
        }
        
        if (config.enablePrintDisable) {
            disablePrinting();
        }

        // Additional protections
        disableImageSaving();
        disableDragAndDrop();
        monitorPageVisibility();
        clearConsoleRegularly();
    }

    // Disable right-click context menu
    function disableRightClick() {
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showSecurityAlert('Right-click is disabled for security reasons.');
            return false;
        });
    }

    // Disable keyboard shortcuts
    function disableKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // F12 (Developer Tools)
            if (e.keyCode === 123) {
                e.preventDefault();
                showSecurityAlert('Developer tools access is restricted.');
                return false;
            }
            
            // Ctrl+Shift+I (Developer Tools)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
                e.preventDefault();
                showSecurityAlert('Developer tools access is restricted.');
                return false;
            }
            
            // Ctrl+U (View Source)
            if (e.ctrlKey && e.keyCode === 85) {
                e.preventDefault();
                showSecurityAlert('View source is disabled for security reasons.');
                return false;
            }
            
            // Ctrl+S (Save Page)
            if (e.ctrlKey && e.keyCode === 83) {
                e.preventDefault();
                showSecurityAlert('Saving page is not allowed.');
                return false;
            }
            
            // Ctrl+Shift+C (Inspect Element)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 67) {
                e.preventDefault();
                showSecurityAlert('Inspect element is disabled.');
                return false;
            }
            
            // Ctrl+Shift+J (Console)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 74) {
                e.preventDefault();
                showSecurityAlert('Console access is restricted.');
                return false;
            }
            
            // Ctrl+A (Select All)
            if (e.ctrlKey && e.keyCode === 65) {
                e.preventDefault();
                showSecurityAlert('Select all is disabled.');
                return false;
            }
            
            // Ctrl+P (Print)
            if (e.ctrlKey && e.keyCode === 80) {
                e.preventDefault();
                showSecurityAlert('Printing is not allowed.');
                return false;
            }
            
            // Ctrl+Shift+K (Firefox Console)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 75) {
                e.preventDefault();
                showSecurityAlert('Console access is restricted.');
                return false;
            }
        });
    }

    // Detect developer tools
    function detectDevTools() {
        let devtools = {
            open: false,
            orientation: null
        };

        setInterval(function() {
            if (window.outerHeight - window.innerHeight > config.devToolsThreshold || 
                window.outerWidth - window.innerWidth > config.devToolsThreshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    showDevToolsWarning();
                }
            } else {
                devtools.open = false;
            }
        }, 500);
    }

    // Disable text selection
    function disableTextSelection() {
        document.addEventListener('selectstart', function(e) {
            // Allow selection in input fields
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return true;
            }
            e.preventDefault();
            return false;
        });
    }

    // Show console warning
    function showConsoleWarning() {
        console.log('%cSTOP!', 'color: red; font-size: 50px; font-weight: bold;');
        console.log('%cThis is a browser feature intended for developers. If someone told you to copy-paste something here to enable a feature or "hack" someone\'s account, it is a scam and will give them access to your account.', 'color: red; font-size: 16px;');
        console.log('%cSee https://en.wikipedia.org/wiki/Self-XSS for more information.', 'color: red; font-size: 16px;');
        console.log('%c⚠️ SECURITY WARNING: Unauthorized access attempts are logged and monitored.', 'color: orange; font-size: 14px; font-weight: bold;');
    }

    // Anti-debugging protection
    function enableAntiDebugging() {
        setInterval(function() {
            const start = new Date();
            debugger;
            const end = new Date();
            if (end - start > 100) {
                showDebuggerWarning();
            }
        }, config.debuggerCheckInterval);
    }

    // Disable printing
    function disablePrinting() {
        window.addEventListener('beforeprint', function(e) {
            e.preventDefault();
            showSecurityAlert('Printing is disabled for security reasons.');
            return false;
        });
    }

    // Disable image saving
    function disableImageSaving() {
        document.addEventListener('dragstart', function(e) {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
                return false;
            }
        });
    }

    // Disable drag and drop
    function disableDragAndDrop() {
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });
        
        document.addEventListener('drop', function(e) {
            e.preventDefault();
            return false;
        });
    }

    // Monitor page visibility
    function monitorPageVisibility() {
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                document.title = 'Secure Page - Access Restricted';
            } else {
                // Restore original title (you may want to customize this)
                document.title = document.title.replace('Secure Page - Access Restricted', 'Indonesian PDF Letter Generator');
            }
        });
    }

    // Clear console regularly
    function clearConsoleRegularly() {
        setInterval(function() {
            console.clear();
            if (config.enableConsoleWarning) {
                showConsoleWarning();
            }
        }, config.consoleCheckInterval);
    }

    // Show security alert
    function showSecurityAlert(message) {
        // Create a subtle notification instead of intrusive alert
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4444;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            font-family: Arial, sans-serif;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(function() {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    // Show developer tools warning
    function showDevToolsWarning() {
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 99999;
            font-family: Arial, sans-serif;
        `;
        
        overlay.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 20px; color: #ff4444;">🛡️</div>
                <div>Access Denied</div>
                <div style="font-size: 16px; opacity: 0.8; margin-top: 10px;">Developer Tools Detected</div>
                <div style="font-size: 14px; opacity: 0.6; margin-top: 20px;">This action has been logged for security purposes.</div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        // Log the attempt
        console.warn('Security Alert: Developer tools access attempt detected at ' + new Date().toISOString());
    }

    // Show debugger warning
    function showDebuggerWarning() {
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 99999;
            font-family: Arial, sans-serif;
        `;
        
        overlay.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 20px; color: #ff4444;">🐛</div>
                <div>Access Denied</div>
                <div style="font-size: 16px; opacity: 0.8; margin-top: 10px;">Debugging Detected</div>
                <div style="font-size: 14px; opacity: 0.6; margin-top: 20px;">This action has been logged for security purposes.</div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        // Log the attempt
        console.warn('Security Alert: Debugging attempt detected at ' + new Date().toISOString());
    }

    // Obfuscate critical functions
    const obfuscatedFunctions = {
        // Base64 encoded function names to make reverse engineering harder
        'Y29uc29sZS5sb2c=': console.log,
        'Y29uc29sZS53YXJu': console.warn,
        'Y29uc29sZS5lcnJvcg==': console.error
    };

    // Additional protection: Monitor for common hacking attempts
    window.addEventListener('message', function(e) {
        // Log suspicious postMessage attempts
        console.warn('Security Alert: Suspicious postMessage detected:', e.origin, e.data);
    });

    // Protect against common XSS vectors
    if (window.location.hash.includes('<script>') || 
        window.location.search.includes('<script>') ||
        document.referrer.includes('<script>')) {
        console.error('Security Alert: Potential XSS attempt detected');
        window.location.href = '/';
    }

})();
