jQuery(document).ready(function ($) {

    /* ─── Toast Helper ─── */
    function gnnToast(message, type) {
        type = type || 'success';
        var $toast = $('<div></div>')
            .addClass('gnn-toast gnn-toast-' + type)
            .text(message);
        $('body').append($toast);
        setTimeout(function () {
            $toast.fadeOut(300, function () { $(this).remove(); });
        }, 3000);
    }

    /* ─── URL Shortening Form ─── */
    $('#gnn-shortner-form').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'gnn_shortner');
        formData.append('nonce', gnn_vars.nonce);

        var $btn = $('#gnn-submit-btn');
        var $btnText = $btn.find('.btn-text');
        var $btnLoader = $btn.find('.btn-loader');

        $btn.prop('disabled', true);
        $btnText.hide();
        $btnLoader.show();

        $.ajax({
            url: gnn_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
                if (response.success) {
                    $('#gnn-result').html('<code>' + response.data.short_url + '</code>');
                } else {
                    gnnToast(response.data, 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
                gnnToast('An error occurred. Please try again.', 'error');
            }
        });
    });

    /* ─── Password Verification Form ─── */
    $('#gnn-password-form').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'gnn_verify_password');
        formData.append('nonce', gnn_vars.nonce);

        var $btn = $(this).find('#gnn-submit-btn');
        var $btnText = $btn.find('.btn-text');
        var $btnLoader = $btn.find('.btn-loader');

        $btn.prop('disabled', true);
        $btnText.hide();
        $btnLoader.show();

        $.ajax({
            url: gnn_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    $btn.prop('disabled', false);
                    $btnText.show();
                    $btnLoader.hide();
                    gnnToast(response.data, 'error');
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.reset();
                    }
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                $btnText.show();
                $btnLoader.hide();
                gnnToast('An error occurred. Please try again.', 'error');
                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset();
                }
            }
        });
    });

    /* ─── Copy to Clipboard ─── */
    $(document).on('click', '#gnn-result code', function () {
        var url = $(this).text();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function () {
                gnnToast('Copied to clipboard: ' + url);
            });
        }
    });

    /* ─── Delete URL ─── */
    var deleteId = null;

    $(document).on('click', '.gnn-delete-url', function (e) {
        e.preventDefault();
        deleteId = $(this).data('id');
        $('#gnn-delete-modal').addClass('gnn-active');
    });

    $('#gnn-delete-yes').on('click', function () {
        if (!deleteId) return;

        $.ajax({
            url: gnn_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'gnn_delete_url',
                nonce: gnn_vars.nonce,
                id: deleteId
            },
            success: function (response) {
                if (response.success) {
                    $('tr[data-id="' + deleteId + '"]').fadeOut(200, function () { $(this).remove(); });
                    $('#gnn-delete-modal').removeClass('gnn-active');
                    deleteId = null;
                    gnnToast('URL deleted successfully.');
                } else {
                    gnnToast(response.data || 'Failed to delete.', 'error');
                }
            },
            error: function () {
                gnnToast('Request failed. Please try again.', 'error');
            }
        });
    });

    $('#gnn-delete-no').on('click', function () {
        $('#gnn-delete-modal').removeClass('gnn-active');
        deleteId = null;
    });

    // Close delete modal on overlay click
    $(document).on('click', '#gnn-delete-modal', function (e) {
        if ($(e.target).is('#gnn-delete-modal')) {
            $('#gnn-delete-modal').removeClass('gnn-active');
            deleteId = null;
        }
    });

    /* ─── Edit URL ─── */
    $(document).on('click', '.gnn-edit-url', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var row = $('tr[data-id="' + id + '"]');
        var short_url = row.find('.gnn-short-link').attr('href').replace(window.location.origin + '/', '');
        var long_url = row.find('.gnn-long-url').attr('title');

        $('#gnn-edit-modal input[name="id"]').val(id);
        $('#gnn-edit-short-url').val(short_url);
        $('#gnn-edit-long-url').val(long_url);
        $('#gnn-edit-modal').addClass('gnn-active');
    });

    $('#gnn-edit-cancel, #gnn-edit-close').on('click', function () {
        $('#gnn-edit-modal').removeClass('gnn-active');
    });

    // Close edit modal on overlay click
    $(document).on('click', '#gnn-edit-modal', function (e) {
        if ($(e.target).is('#gnn-edit-modal')) {
            $('#gnn-edit-modal').removeClass('gnn-active');
        }
    });

    $('#gnn-edit-url-form').on('submit', function (e) {
        e.preventDefault();
        var formData = {
            action: 'gnn_edit_url',
            nonce: gnn_vars.nonce,
            id: $(this).find('input[name="id"]').val(),
            short_url: $(this).find('input[name="short_url"]').val(),
            long_url: $(this).find('input[name="long_url"]').val()
        };

        $.ajax({
            url: gnn_vars.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    var row = $('tr[data-id="' + formData.id + '"]');
                    row.find('.gnn-short-link').attr('href', response.data.short_url).text(response.data.short_url);
                    row.find('.gnn-long-url').attr('title', response.data.long_url).text(response.data.long_url);
                    $('#gnn-edit-modal').removeClass('gnn-active');
                    gnnToast('URL updated successfully.');
                } else {
                    gnnToast(response.data || 'Failed to update.', 'error');
                }
            },
            error: function () {
                gnnToast('Request failed. Please try again.', 'error');
            }
        });
    });

    /* ─── Keyboard Accessibility ─── */
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $('#gnn-delete-modal').removeClass('gnn-active');
            $('#gnn-edit-modal').removeClass('gnn-active');
            deleteId = null;
        }
    });
});