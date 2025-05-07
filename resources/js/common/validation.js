import $ from 'jquery';

export const Validation = {
    init: function() {
        this.handleNumber();
        this.handleTrimSpace();
    },

    handleNumber: function() {
        $('.input-number').on('input', function() {
            let value = $(this).val();
            // Chỉ cho phép nhập số
            value = value.replace(/[^0-9]/g, '');
            $(this).val(value);
        });
    },
    
    handleTrimSpace: function() {
        $('.trim-space').on('input', function() {
            $(this).val($(this).val().replace(/\s+/g, ''));
        });
    }
};

// Make Validation available globally
window.Validation = Validation; 