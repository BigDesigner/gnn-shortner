jQuery(document).ready(function($) {
    // URL Kısaltma Formu
    $('#gnn-shortner-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'gnn_shortner');
        formData.append('nonce', gnn_vars.nonce);

        $.ajax({
            url: gnn_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#gnn-result').html('<code>' + response.data.short_url + '</code>');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Kısa URL'ye tıklama (Yeni bildirim kutusu)
    $(document).on('click', '#gnn-result code', function() {
        var url = $(this).text();
        navigator.clipboard.writeText(url);

        // Bildirim kutusunu oluştur
        var $notification = $('<div class="gnn-notification">Panoya kopyalandı: ' + url + '</div>');
        $('body').append($notification);

        // 5 saniye sonra kaybolmasını sağla
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    });

    // Delete URL
    var deleteId = null;
    $(document).on('click', '.gnn-delete-url', function(e) {
        e.preventDefault();
        deleteId = $(this).data('id');
        console.log('Delete clicked, ID:', deleteId);
        $('#gnn-delete-modal').show();
    });

    $('#gnn-delete-yes').on('click', function() {
        if (!deleteId) return;
        console.log('Yes clicked, deleting ID:', deleteId);
        $.ajax({
            url: gnn_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'gnn_delete_url',
                nonce: gnn_vars.nonce,
                id: deleteId
            },
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    $('tr[data-id="' + deleteId + '"]').remove();
                    $('#gnn-delete-modal').hide();
                    deleteId = null;
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', status, error);
                alert('AJAX request failed: ' + error);
            }
        });
    });

    $('#gnn-delete-no').on('click', function() {
        console.log('No clicked, closing modal');
        $('#gnn-delete-modal').hide();
        deleteId = null;
    });

    // Edit URL
    $(document).on('click', '.gnn-edit-url', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var row = $('tr[data-id="' + id + '"]');
        var short_url = row.find('td:eq(0)').text().replace(window.location.origin + '/', '');
        var long_url = row.find('td:eq(1)').text();

        $('#gnn-edit-form input[name="id"]').val(id);
        $('#gnn-edit-form input[name="short_url"]').val(short_url);
        $('#gnn-edit-form input[name="long_url"]').val(long_url);
        $('#gnn-edit-form').show();
    });

    $('#gnn-edit-url-form').on('submit', function(e) {
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
            success: function(response) {
                if (response.success) {
                    var row = $('tr[data-id="' + formData.id + '"]');
                    row.find('td:eq(0)').text(response.data.short_url);
                    row.find('td:eq(1)').text(response.data.long_url);
                    $('#gnn-edit-form').hide();
                    alert('URL updated successfully.');
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    });
});