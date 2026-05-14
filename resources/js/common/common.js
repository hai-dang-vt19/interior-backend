import $ from 'jquery';

export const Common = {
    init: function() {
        this.handleFormErrors();
        this.handleFormLoading();
        this.handlePerPageSelect();
        this.handleResetForm();
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
            if ($(this).closest('#siteAuthModal').length) {
                return;
            }
            Loading.show();
        });
    },

    handlePerPageSelect: function() {
        $('#per_page_select').on('change', function() {
            if ($(this).data('submit-form')) {
                $('#per_page').val($(this).val());
                $($(this).data('submit-form')).submit();
            }
        });

        $('#focus_page_loading .page-link').on('click', function() {
            Loading.show();
        });
    },

    handleResetForm: function() {
        $('.reset-form').on('click', function() {
            $('#per_page').val('');
            let getForm = $(this).closest('form');

            getForm.find('input').each(function() {
                $(this).val('');
            });
            getForm.find('textarea').each(function() {
                $(this).val('');
            });
        });
    }
};

// Make Common available globally
window.Common = Common;
