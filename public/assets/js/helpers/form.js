/**
 * Form Helper
 * A reusable JavaScript utility for handling form submissions
 *
 * Usage:
 *   Form.submit('#user-form', {
 *       success: function(response) { ... },
 *       error: function(xhr) { ... }
 *   });
 */

// Prevent duplicate class declaration
if (typeof FormHelper === 'undefined') {
    class FormHelper {
    /**
     * Submit form via AJAX
     */
    submit(formSelector, options = {}) {
        const form = $(formSelector);
        if (!form.length) {
            console.error(`Form "${formSelector}" not found`);
            return;
        }

        const formAction = form.attr('action');
        const formMethod = form.find('input[name="_method"]').val() || form.attr('method') || 'POST';
        const hasFileInput = form.find('input[type="file"]').length > 0;

        // Clear previous errors
        this.clearErrors(form);

        // Check if form has file input
        let ajaxData;
        let ajaxOptions = {
            url: formAction,
            method: formMethod === 'PUT' || formMethod === 'PATCH' ? 'POST' : formMethod,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
                ...options.headers
            },
            success: function(response) {
                if (options.success) {
                    options.success(response);
                } else {
                    // Default success handler
                    if (window.Toast) {
                        window.Toast.success(response.message || 'Operation completed successfully.');
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    FormHelper.displayValidationErrors(form, errors);

                    if (window.Toast) {
                        window.Toast.error(xhr.responseJSON.message || 'Validation failed.');
                    }
                } else {
                    if (window.Toast) {
                        window.Toast.error('An error occurred. Please try again.');
                    }
                }

                if (options.error) {
                    options.error(xhr);
                }
            }
        };

        if (hasFileInput) {
            // Use FormData for file uploads
            const formData = new FormData(form[0]);
            
            // Add _method for PUT/PATCH requests
            if (formMethod === 'PUT' || formMethod === 'PATCH') {
                formData.append('_method', formMethod);
            }

            ajaxOptions.data = formData;
            ajaxOptions.processData = false;
            ajaxOptions.contentType = false;
        } else {
            // Use serialize for regular forms
            ajaxOptions.data = form.serialize() + (formMethod === 'PUT' || formMethod === 'PATCH' ? `&_method=${formMethod}` : '');
        }

        $.ajax(ajaxOptions);
    }

    /**
     * Display validation errors
     */
    static displayValidationErrors(form, errors) {
        $.each(errors, function(key, value) {
            const input = form.find(`[name="${key}"]`);
            input.addClass('is-invalid');

            // Find or create error message element
            let errorDiv = form.find(`#${key}-error`);
            if (!errorDiv.length) {
                errorDiv = $(`<div class="invalid-feedback" id="${key}-error"></div>`);
                input.after(errorDiv);
            }
            errorDiv.removeClass('d-none').text(value[0]);
        });
    }

    /**
     * Clear form errors
     */
    clearErrors(form) {
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').addClass('d-none').text('');
    }

    /**
     * Reset form
     */
    reset(formSelector) {
        const form = $(formSelector);
        if (form.length) {
            form[0].reset();
            this.clearErrors(form);
        }
    }

    /**
     * Validate form using HTML5 validation
     * @param {HTMLElement|jQuery|string} formElement - Form element, jQuery object, or selector
     * @returns {boolean} - True if form is valid
     */
    validate(formElement) {
        const form = formElement instanceof jQuery ? formElement[0] : 
                     typeof formElement === 'string' ? document.querySelector(formElement) : 
                     formElement;
        
        if (!form || form.tagName !== 'FORM') {
            console.error('Invalid form element provided to Form.validate()');
            return false;
        }

        // Use HTML5 validation
        if (form.checkValidity && typeof form.checkValidity === 'function') {
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            return true;
        }

        // Fallback: basic validation
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value || (field.type === 'checkbox' && !field.checked)) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return isValid;
    }

    /**
     * Show validation errors on form
     * @param {string} formSelector - Form selector
     * @param {object} errors - Object with field names as keys and error messages as values
     */
    showErrors(formSelector, errors) {
        const form = $(formSelector);
        if (!form.length) {
            console.error(`Form "${formSelector}" not found`);
            return;
        }

        // Clear previous errors
        this.clearErrors(form);

        // Display new errors
        FormHelper.displayValidationErrors(form, errors);
    }
    }

    // Create global instance only if it doesn't exist
    if (typeof window.Form === 'undefined') {
        window.Form = new FormHelper();
    }
}

