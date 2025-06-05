jQuery(document).ready(function($) {
    // Handle dismissible notices
    $('.notice-dismiss').on('click', function() {
        var $notice = $(this).parent();
        if ($notice.hasClass('is-dismissible')) {
            var noticeId = $notice.attr('id');
            if (noticeId) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'dismiss_ruthless_promo',
                        notice_id: noticeId,
                        nonce: lizaSpotifyAdmin.nonce
                    }
                });
            }
        }
    });

    // Add any additional admin-specific JavaScript functionality here
}); 