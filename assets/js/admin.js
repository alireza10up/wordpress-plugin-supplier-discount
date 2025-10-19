/**
 * Admin JavaScript for Supplier Discount Plugin
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Validate discount percentage input for both simple and variable products
        validateAllDiscountInputs();
        
        // Re-validate when variations are added/updated
        $(document).on('woocommerce_variations_added', function() {
            validateAllDiscountInputs();
        });

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
    function validateAllDiscountInputs() {
        $('input[name*="xyz_supplier_discount_percent"]').off('blur input').on('blur', function() {
            validateDiscountInput($(this));
        }).on('input', function() {
            validateDiscountInput($(this));
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
        var numValue = parseFloat(value);

        // Check if it's a valid number
        if (isNaN(numValue)) {
            showError($input, 'لطفاً یک عدد معتبر وارد کنید.');
            return false;
        }

        // Check range
        if (numValue < 0 || numValue > 100) {
            showError($input, 'درصد تخفیف باید بین 0 تا 100 باشد.');
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
