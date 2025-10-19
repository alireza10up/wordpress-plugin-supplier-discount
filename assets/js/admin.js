/**
 * Admin JavaScript for Supplier Discount Plugin
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize validation
        initValidation();
        
        // Re-validate when variations are added/updated
        $(document).on('woocommerce_variations_added', function() {
            setTimeout(initValidation, 100);
        });
        
        // Also re-initialize when variations are loaded
        $(document).on('woocommerce_variations_loaded', function() {
            setTimeout(initValidation, 100);
        });
        
        // Re-initialize every 2 seconds to catch dynamically added inputs
        setInterval(function() {
            var currentCount = $('input[name*="xyz_supplier_discount_percent"]').length;
            if (currentCount > 0) {
                initValidation();
            }
        }, 2000);

        // Validate on form submission
        $('form#post, form#variable_product_options').on('submit', function(e) {
            var hasError = false;
            
            $('input[name*="xyz_supplier_discount_percent"]').each(function() {
                if (!validateDiscountInput($(this))) {
                    hasError = true;
                }
            });

            if (hasError) {
                e.preventDefault();
                alert('لطفاً مقادیر تخفیف را بررسی کنید. باید بین 0 تا 100 باشد.');
                return false;
            }
        });
    });

    /**
     * Initialize validation for all discount inputs
     */
    function initValidation() {
        // Remove existing event handlers
        $('input[name*="xyz_supplier_discount_percent"]').off('blur.xyz input.xyz');
        
        // Add new event handlers
        $('input[name*="xyz_supplier_discount_percent"]').on('blur.xyz', function() {
            console.log('Blur event triggered for:', $(this).attr('name'));
            validateDiscountInput($(this));
        }).on('input.xyz', function() {
            console.log('Input event triggered for:', $(this).attr('name'));
            validateDiscountInput($(this));
        });
        
        console.log('Validation initialized for', $('input[name*="xyz_supplier_discount_percent"]').length, 'inputs');
        
        // Debug: log all found inputs
        $('input[name*="xyz_supplier_discount_percent"]').each(function() {
            console.log('Found input:', $(this).attr('name'), $(this).attr('id'));
        });
    }

    /**
     * Validate discount percentage input
     *
     * @param {jQuery} $input Input element
     * @return {boolean} Is valid
     */
    function validateDiscountInput($input) {
        var value = $input.val();
        
        // Clear existing error
        clearError($input);

        // Allow empty values
        if (value === '') {
            return true;
        }

        // Convert to number
        var numValue = parseInt(value, 10);

        // Check if it's a valid integer
        if (isNaN(numValue) || !Number.isInteger(parseFloat(value))) {
            showError($input, 'لطفاً یک عدد صحیح معتبر وارد کنید.');
            return false;
        }

        // Check range (1 to 100)
        if (numValue < 1 || numValue > 100) {
            showError($input, 'درصد تخفیف باید بین 1 تا 100 باشد.');
            return false;
        }

        return true;
    }

    /**
     * Show error message
     *
     * @param {jQuery} $input Input element
     * @param {string} message Error message
     */
    function showError($input, message) {
        var $error = $input.siblings('.xyz-supplier-discount-error');
        
        if ($error.length === 0) {
            $error = $('<div class="xyz-supplier-discount-error"></div>');
            $input.after($error);
        }
        
        $error.text(message).addClass('show');
        $input.addClass('error');
    }

    /**
     * Clear error message
     *
     * @param {jQuery} $input Input element
     */
    function clearError($input) {
        $input.siblings('.xyz-supplier-discount-error').removeClass('show').text('');
        $input.removeClass('error');
    }

})(jQuery);
