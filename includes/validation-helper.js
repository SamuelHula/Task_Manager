// Additional validation functions that can be included on any page

/**
 * Validate email format
 */
function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

/**
 * Validate phone number (basic international format)
 */
function validatePhone(phone) {
    const phonePattern = /^\+?[1-9]\d{1,14}$/;
    return phonePattern.test(phone.replace(/[\s\-\(\)]/g, ''));
}

/**
 * Validate URL format
 */
function validateURL(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

/**
 * Validate numeric input with range
 */
function validateNumber(input, min = null, max = null) {
    const num = parseFloat(input);
    if (isNaN(num)) return false;
    if (min !== null && num < min) return false;
    if (max !== null && num > max) return false;
    return true;
}

/**
 * Validate password strength
 */
function validatePassword(password) {
    const minLength = 8;
    if (password.length < minLength) return false;
    
    // At least one uppercase letter
    if (!/[A-Z]/.test(password)) return false;
    
    // At least one lowercase letter
    if (!/[a-z]/.test(password)) return false;
    
    // At least one number
    if (!/\d/.test(password)) return false;
    
    // At least one special character
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) return false;
    
    return true;
}

/**
 * Sanitize input by removing potentially dangerous characters
 */
function sanitizeInput(input) {
    return input
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/`/g, '&#096;')
        .replace(/\$/g, '&#036;')
        .replace(/\|/g, '&#124;')
        .replace(/\\/g, '&#092;');
}

/**
 * Format input as user types (for phone numbers, dates, etc.)
 */
function formatPhoneInput(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    
    if (value.length >= 6) {
        value = `(${value.substring(0, 3)}) ${value.substring(3, 6)}-${value.substring(6)}`;
    } else if (value.length >= 3) {
        value = `(${value.substring(0, 3)}) ${value.substring(3)}`;
    }
    
    input.value = value;
}

/**
 * Show/hide password with toggle
 */
function setupPasswordToggle(passwordFieldId, toggleButtonId) {
    const passwordField = document.getElementById(passwordFieldId);
    const toggleButton = document.getElementById(toggleButtonId);
    
    if (passwordField && toggleButton) {
        toggleButton.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }
}

/**
 * Debounce function for input validation
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Auto-sanitize inputs with data-sanitize attribute
document.addEventListener('DOMContentLoaded', function() {
    const sanitizableInputs = document.querySelectorAll('[data-sanitize]');
    
    sanitizableInputs.forEach(input => {
        input.addEventListener('blur', function() {
            this.value = sanitizeInput(this.value);
        });
    });
    
    // Auto-format phone inputs
    const phoneInputs = document.querySelectorAll('[data-format="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            formatPhoneInput(this);
        });
    });
});