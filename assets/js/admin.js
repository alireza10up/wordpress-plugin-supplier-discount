/**
 * Admin JavaScript for Supplier Discount Plugin
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Validate discount percentage input
        $('input[name*="xyz_supplier_discount_percent"]').on('blur', function() {
            validateDiscountInput($(this));
        });

        // Real-time validation on input
        $('input[name*="xyz_supplier_discount_percent"]').on('input', function() {
            validateDiscountInput($(this));
        });

        // Validate on form submission
        $('form#post').on('submit', function(e) {
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
     * Validate discount percentage input
     *
     * @param {jQuery} $input Input element
     * @return {boolean} Is valid
     */
    function validateDiscountInput($input) {
        var value = $input.val();
        var $error = $input.siblings('.xyz-supplier-discount-error');
        
        // Remove existing error
        $error.removeClass('show').text('');

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
    }

})(jQuery);
