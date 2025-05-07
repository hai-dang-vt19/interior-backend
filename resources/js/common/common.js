import $ from 'jquery';

export const Common = {
    init: function() {
        this.handleFormErrors();
        this.handleFormLoading();
    },

    handleFormErrors: function() {
        // Xóa class error khi focus vào input
        $('input, select, textarea').on('focus', function() {
            if ($(this).hasClass('is-invalid')) {
                $(this).removeClass('is-invalid');
                $(`#${$(this).attr('id')}-error`).text('');
            }
        });
    },

    handleFormLoading: function() {
        $('form').on('submit', function() {
            Loading.show();
        });
    }
};

// Make Common available globally
window.Common = Common; 