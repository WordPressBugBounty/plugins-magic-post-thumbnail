jQuery(function($) {
    if (typeof mptPostsList === 'undefined') {
        return;
    }

    var mediaFrame;
    var mediaCtx = {};

    function mptApplyThumb($cell, data) {
        if (!data) {
            return;
        }
        var $thumb = $cell.find('.mpt-list-thumb');
        if (data.html) {
            $thumb.html(data.html);
        }
        if (typeof data.thumb !== 'undefined') {
            $cell.attr('data-thumb', data.thumb);
        }
        if (data.label) {
            $cell.find('.mpt-list-generate').text(data.label);
        }
    }

    function mptOpenFeaturedMedia($cell) {
        if (typeof wp === 'undefined' || !wp.media) {
            return;
        }
        var postId = parseInt($cell.data('post'), 10);
        var thumbId = parseInt($cell.attr('data-thumb'), 10) || 0;
        if (!postId) {
            return;
        }
        mediaCtx = { postId: postId, thumbId: thumbId, $cell: $cell };

        if (!mediaFrame) {
            var l10n = (wp.media.view && wp.media.view.l10n) ? wp.media.view.l10n : {};
            mediaFrame = wp.media({
                frame: 'select',
                title: l10n.setFeaturedImageTitle || '',
                library: { type: 'image' },
                button: { text: l10n.setFeaturedImage || '' },
                multiple: false
            });
            mediaFrame.on('open', function() {
                var selection = mediaFrame.state().get('selection');
                selection.reset();
                if (!mediaCtx.thumbId) {
                    return;
                }
                var attachment = wp.media.attachment(mediaCtx.thumbId);
                attachment.fetch();
                selection.add(attachment);
            });
            mediaFrame.on('select', function() {
                var att = mediaFrame.state().get('selection').first();
                if (!att || !mediaCtx.postId) {
                    return;
                }
                var $target = mediaCtx.$cell;
                $target.addClass('is-busy');
                $.post(mptPostsList.ajaxurl, {
                    action: 'mpt_set_featured_from_list',
                    nonce: mptPostsList.nonce,
                    post_id: mediaCtx.postId,
                    thumbnail_id: att.id
                }).done(function(res) {
                    if (res && res.success) {
                        mptApplyThumb($target, res.data);
                    }
                }).always(function() {
                    $target.removeClass('is-busy');
                });
            });
        }

        mediaFrame.open();
    }

    $(document).on('click', '.mpt-list-thumb', function(e) {
        e.preventDefault();
        var $cell = $(this).closest('.mpt-list-cell');
        if ($cell.hasClass('is-busy')) {
            return;
        }
        mptOpenFeaturedMedia($cell);
    });

    $(document).on('click', '.mpt-list-generate', function() {
        var $btn  = $(this);
        var $cell = $btn.closest('.mpt-list-cell');
        var id    = $cell.data('post');
        if (!id || $cell.hasClass('is-busy')) {
            return;
        }
        $cell.addClass('is-busy');
        $btn.text(mptPostsList.generating);
        $.post(mptPostsList.ajaxurl, {
            action: 'mpt_generate_from_list',
            nonce: mptPostsList.nonce,
            post_id: id
        }).done(function(res) {
            if (!res || !res.success) {
                $btn.text(mptPostsList.error);
                $cell.removeClass('is-busy');
                return;
            }
            mptApplyThumb($cell, res.data);
            $cell.removeClass('is-busy');
        }).fail(function() {
            $btn.text(mptPostsList.error);
            $cell.removeClass('is-busy');
        });
    });
});
