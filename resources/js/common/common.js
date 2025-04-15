import $ from 'jquery';

export const Common = {
    init: function() {
        this.handleFormErrors();
    },

    handleFormErrors: function() {
        // Xóa class error khi focus vào input
        $('input, select, textarea').on('focus', function() {
            if ($(this).hasClass('is-invalid')) {
                $(this).removeClass('is-invalid');
                $(`#${$(this).attr('id')}-error`).text('');
            }
        });
    }
};

// Make Common available globally
window.Common = Common; 