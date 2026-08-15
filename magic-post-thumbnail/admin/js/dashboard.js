jQuery(function($) {
    if (typeof mptDashboard === 'undefined') {
        return;
    }

    $(document).on('click', '#mpt-health-test', function() {
        var $btn = $(this);
        var $out = $('#mpt-health-test-result');
        if ($btn.prop('disabled') || $btn.hasClass('is-busy')) {
            return;
        }
        if ($btn.data('ai') && mptDashboard.aiConfirm) {
            if (!window.confirm(mptDashboard.aiConfirm)) {
                return;
            }
        }
        $btn.addClass('is-busy').prop('disabled', true);
        var original = $btn.text();
        $btn.text(mptDashboard.generating);
        $out.attr('hidden', true).empty();
        $.ajax({
            url: mptDashboard.ajaxurl,
            method: 'POST',
            timeout: 120000,
            data: {
                action: 'mpt_dashboard_test_generate',
                nonce: mptDashboard.nonce
            }
        }).done(function(res) {
            if (!res || !res.success) {
                $out.removeAttr('hidden').html('<p class="mpt-health-test-error">' + mptDashboard.error + '</p>');
                return;
            }
            var html = '';
            if (res.data && res.data.html) {
                html += '<div class="mpt-health-test-thumb">' + res.data.html + '</div>';
            }
            if (res.data && res.data.edit_url) {
                html += '<p><a href="' + res.data.edit_url + '">' + (res.data.view || '') + '</a></p>';
            }
            $out.removeAttr('hidden').html(html);
        }).fail(function() {
            $out.removeAttr('hidden').html('<p class="mpt-health-test-error">' + mptDashboard.error + '</p>');
        }).always(function() {
            $btn.removeClass('is-busy').prop('disabled', false).text(original);
        });
    });
});
