function mptCurrentAutoChain() {
    var chain = [];
    var boxes = document.querySelectorAll('#mpt-sortable-auto .ordered-checkbox:checked:not(:disabled)');
    boxes.forEach(function(input) {
        if (input.value) {
            chain.push(input.value);
        }
    });
    return chain;
}

function mptSelectedBankItem() {
    return document.querySelector('.mpt-bank-item.is-selected');
}

function mptSelectedBankSlug() {
    var item = mptSelectedBankItem();
    return item ? (item.getAttribute('data-bank') || '') : '';
}

function mptSelectedBankLabel() {
    var item = mptSelectedBankItem();
    return item ? (item.getAttribute('data-label') || '') : '';
}

function mptSelectedBankIsAi() {
    var item = mptSelectedBankItem();
    if (!item) {
        return false;
    }
    var input = item.querySelector('.ordered-checkbox');
    return !!(input && input.getAttribute('data-ai') === '1');
}

function mptSyncPreviewForSelection() {
    if (typeof apisTestingAjax === 'undefined') {
        return;
    }
    var warn = document.getElementById('mpt-preview-ai-warning');
    if (warn) {
        if (mptSelectedBankIsAi()) {
            var tpl = apisTestingAjax.preview_ai_warning || '';
            warn.textContent = tpl.replace('%s', mptSelectedBankLabel());
            warn.hidden = false;
        } else {
            warn.hidden = true;
        }
    }
    var results = document.getElementById('mpt-preview-results');
    if (results) {
        results.innerHTML = '';
    }
}

function mptSyncChainUnsaved() {
    var unsaved = document.getElementById('mpt-chain-unsaved');
    if (!unsaved || typeof apisTestingAjax === 'undefined') {
        return;
    }
    var saved = (apisTestingAjax.preview_saved_chain || '').split(',').filter(Boolean);
    var live  = mptCurrentAutoChain();
    unsaved.hidden = (saved.join('|') === live.join('|'));
}

function mptRenumberChain() {
    var $ = jQuery;
    var $active = $('#mpt-sortable-auto');

    $active.find('.mpt-bank-item').each(function(index) {
        $(this).find('.mpt-bank-pos').text(index + 1);
        $(this).find('.ordered-checkbox').attr('data-order', index + 1);
    });
    $('#mpt-banks-inactive .mpt-bank-pos').text('');
    $('#mpt-chain-empty').prop('hidden', $active.find('.mpt-bank-item').length > 0);
}

function mptSelectBank(slug) {
    var $ = jQuery;
    var $item = $('.mpt-bank-item[data-bank="' + slug + '"]');
    var panel = $item.attr('data-panel');
    if (!$item.length || !panel) {
        return;
    }

    if ($item.hasClass('is-selected')) {
        return;
    }

    $('.mpt-bank-item').removeClass('is-selected');
    $item.addClass('is-selected');

    $('#mpt-bank-settings .mpt-bank-panel').hide();
    $('#' + panel).show();

    var status = $item.attr('data-status') || 'untested';
    $('#mpt-bank-settings-title').text($item.attr('data-label') || slug);
    $('#mpt-bank-settings-status')
        .attr('class', 'mpt-key-badge mpt-key-' + status)
        .text($item.attr('data-status-label') || '');

    mptSyncPreviewForSelection();
}

jQuery(function($) {

    // Drag & Drop Banks — the name is the handle so a click on it never toggles the checkbox.
    var mptBankJustSorted = false;
    $("#mpt-sortable-auto").sortable({
        handle: '.mpt-bank-name, .mpt-bank-pos',
        cancel: 'input, label, button',
        distance: 5,
        stop: function() {
            mptBankJustSorted = true;
            mptRenumberChain();
            mptSyncChainUnsaved();
            setTimeout(function() { mptBankJustSorted = false; }, 0);
        }
    });
    $("#mpt-sortable-auto").disableSelection();

    // Checking a bank moves it into the chain, unchecking sends it back below.
    $('#mpt-bank-chain').on('change', '.ordered-checkbox', function() {
        var $item = $(this).closest('.mpt-bank-item');

        if (this.checked) {
            $item.appendTo('#mpt-sortable-auto');
        } else {
            $item.appendTo('#mpt-banks-inactive');
        }

        mptRenumberChain();
        mptSyncChainUnsaved();
    });

    // Title and Configure both open the bank settings panel.
    $('#mpt-bank-chain').on('click', '.mpt-bank-configure, .mpt-bank-name', function() {
        if (mptBankJustSorted) {
            return;
        }
        mptSelectBank($(this).closest('.mpt-bank-item').attr('data-bank'));
    });
    $('#mpt-bank-chain').on('click', '.mpt-bank-item', function(e) {
        if (mptBankJustSorted) {
            return;
        }
        if ($(e.target).closest('label, input, button, .mpt-bank-name, .mpt-bank-pos').length) {
            return;
        }
        mptSelectBank($(this).attr('data-bank'));
    });

    // The server pre-selects the first bank of the chain; only step in if it could not.
    if (!$('.mpt-bank-item.is-selected').length) {
        var first = $('.mpt-bank-item[data-panel!=""]').first();
        if (first.length) {
            mptSelectBank(first.attr('data-bank'));
        }
    }

    mptRenumberChain();
    mptSyncChainUnsaved();
    mptSyncPreviewForSelection();

});






/* BANKS TESTING APIS */

function mptHandleApiTestResult(data, resultEl, apibank) {
    if (data && data.success) {
        resultEl.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
        mptUpdateKeyBadge(apibank, 'valid');
        return;
    }
    var reason = (data && data.data && data.data.reason) ? data.data.reason : '';
    var msg    = apisTestingAjax.error_key;
    if ('billing' === reason && apisTestingAjax.error_billing) {
        msg = apisTestingAjax.error_billing;
    } else if ('connection' === reason) {
        msg = apisTestingAjax.error_testing;
    }
    resultEl.innerHTML = '<span class="text-warning">' + msg + '</span>';
    mptUpdateKeyBadge(apibank, 'invalid');
}

function mptHandleApiTestError(resultEl, apibank) {
    resultEl.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + ' or ' + apisTestingAjax.error_testing + '</span>';
    if (apibank) {
        mptUpdateKeyBadge(apibank, 'invalid');
    }
}

function mptUpdateKeyBadge(apibank, status) {
    if (!apibank) {
        return;
    }
    var labels = (apisTestingAjax.badge_labels) ? apisTestingAjax.badge_labels : {};
    var label  = labels[status] || status;
    var item   = document.querySelector('.mpt-bank-item[data-bank="' + apibank + '"]');
    if (!item) {
        return;
    }

    item.setAttribute('data-status', status);
    item.setAttribute('data-status-label', label);

    var badge = item.querySelector('.mpt-key-badge');
    if (badge) {
        badge.className = 'mpt-key-badge mpt-key-' + status;
        badge.textContent = label;
    }

    if (item.classList.contains('is-selected')) {
        var head = document.getElementById('mpt-bank-settings-status');
        if (head) {
            head.className = 'mpt-key-badge mpt-key-' + status;
            head.textContent = label;
        }
    }
}

function mptBindApiTest(btnId, resultId, inputSelector, apibank) {
    document.addEventListener("DOMContentLoaded", function() {
        const btn      = document.getElementById(btnId);
        const resultEl = document.getElementById(resultId);
        const inputEl  = document.querySelector(inputSelector);
        const loader   = resultEl ? resultEl.querySelector("img") : null;

        if (!btn || !resultEl || !inputEl) {
            return;
        }

        btn.addEventListener("click", function() {
            if (loader) {
                loader.classList.remove("hidden");
            }

            fetch(apisTestingAjax.ajaxurl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "action=test_apis&apibank=" + encodeURIComponent(apibank) + "&nonce=" + apisTestingAjax.nonce + "&apikey=" + encodeURIComponent(inputEl.value)
            })
            .then(response => response.json())
            .then(data => mptHandleApiTestResult(data, resultEl, apibank))
            .catch(() => mptHandleApiTestError(resultEl, apibank));
        });
    });
}

mptBindApiTest("btnPixabay", "resultPixabay", 'input[name="MPT_plugin_banks_settings[pixabay][apikey]"]', "pixabay");
mptBindApiTest("btnGptimage", "resultGptimage", 'input[name="MPT_plugin_banks_settings[gptimage][apikey]"]', "gptimage");
mptBindApiTest("btnYoutube", "resultYoutube", 'input[name="MPT_plugin_banks_settings[youtube][apikey]"]', "youtube");
mptBindApiTest("btnUnsplash", "resultUnsplash", 'input[name="MPT_plugin_banks_settings[unsplash][apikey]"]', "unsplash");
mptBindApiTest("btnPexels", "resultPexels", 'input[name="MPT_plugin_banks_settings[pexels][apikey]"]', "pexels");
mptBindApiTest("btnReplicate", "resultReplicate", 'input[name="MPT_plugin_banks_settings[replicate][apikey]"]', "replicate");
mptBindApiTest("btnIdeogram", "resultIdeogram", 'input[name="MPT_plugin_banks_settings[ideogram][apikey]"]', "ideogram");
mptBindApiTest("btnFlux2", "resultFlux2", 'input[name="MPT_plugin_banks_settings[flux2][apikey]"]', "flux2");
mptBindApiTest("btnGemini", "resultGemini", 'input[name="MPT_plugin_banks_settings[gemini][apikey]"]', "gemini");
mptBindApiTest("btnGrok", "resultGrok", 'input[name="MPT_plugin_banks_settings[grok][apikey]"]', "grok");
mptBindApiTest("btnStability", "resultStability", 'input[name="MPT_plugin_banks_settings[stability][apikey]"]', "stability");

document.addEventListener("DOMContentLoaded", function() {
    var btn     = document.getElementById("mpt-preview-btn");
    var input   = document.getElementById("mpt-preview-keyword");
    var results = document.getElementById("mpt-preview-results");
    if (!btn || !input || !results) {
        return;
    }

    function mptPreviewConfirmAi() {
        return window.confirm(apisTestingAjax.preview_ai_confirm || '');
    }

    function mptRunPreview(confirmAi) {
        var loading = confirmAi && apisTestingAjax.preview_ai_loading
            ? apisTestingAjax.preview_ai_loading
            : (apisTestingAjax.preview_loading || '…');
        results.innerHTML = '<p>' + loading + '</p>';
        var bank = mptSelectedBankSlug();
        if (!bank) {
            results.innerHTML = '<p class="text-warning">' + (apisTestingAjax.preview_error || apisTestingAjax.error_testing) + '</p>';
            return;
        }
        var body = "action=mpt_preview_search&nonce=" + encodeURIComponent(apisTestingAjax.preview_nonce) + "&keyword=" + encodeURIComponent(input.value) + "&bank=" + encodeURIComponent(bank);
        if (confirmAi) {
            body += "&confirm_ai=1";
        }
        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: body
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data || !data.success) {
                results.innerHTML = '<p class="text-warning">' + (apisTestingAjax.preview_error || apisTestingAjax.error_testing) + '</p>';
                return;
            }
            var payload = data.data || {};
            if (payload.needs_confirm) {
                if (!mptPreviewConfirmAi()) {
                    results.innerHTML = '';
                    return;
                }
                mptRunPreview(true);
                return;
            }
            var items = payload.items || [];
            if (payload.message && !items.length) {
                var msg = '<p>' + payload.message + '</p>';
                if (payload.bank_label) {
                    msg = '<p><strong>' + payload.bank_label + '</strong></p>' + msg;
                }
                results.innerHTML = msg;
                return;
            }
            if (!items.length) {
                results.innerHTML = '<p>' + (apisTestingAjax.preview_empty || '') + '</p>';
                return;
            }
            var wrapClass = payload.ai ? 'mpt-preview-results mpt-preview-results-ai' : 'mpt-preview-results';
            var html = '<div class="' + wrapClass + '">';
            items.forEach(function(item) {
                html += '<figure><img src="' + String(item.url || '').replace(/"/g, '&quot;') + '" alt="" />';
                if (item.title && 'bing' !== bank) {
                    html += '<figcaption>' + item.title + '</figcaption>';
                }
                html += '</figure>';
            });
            html += '</div>';
            if (payload.bank_label) {
                html = '<p><strong>' + payload.bank_label + '</strong></p>' + html;
            }
            results.innerHTML = html;
        })
        .catch(function() {
            results.innerHTML = '<p class="text-warning">' + (apisTestingAjax.preview_error || apisTestingAjax.error_testing) + '</p>';
        });
    }

    btn.addEventListener("click", function() {
        if (mptSelectedBankIsAi()) {
            if (!mptPreviewConfirmAi()) {
                return;
            }
            mptRunPreview(true);
            return;
        }
        mptRunPreview(false);
    });
});

jQuery(function($) {
    function mptBindMedia(selectId, clearId, inputId, previewId) {
        var frame;
        $(selectId).on('click', function(e) {
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: $(this).text(),
                button: { text: $(this).text() },
                library: { type: 'image' },
                multiple: false
            });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                $(inputId).val(att.id);
                $(previewId).attr('src', att.url).show();
                $(clearId).show();
            });
            frame.open();
        });
        $(clearId).on('click', function(e) {
            e.preventDefault();
            $(inputId).val('0');
            $(previewId).attr('src', '').hide();
            $(this).hide();
        });
    }
    if (typeof wp !== 'undefined' && wp.media) {
        mptBindMedia('#mpt-fallback-select', '#mpt-fallback-clear', '#mpt-fallback-id', '#mpt-fallback-preview');
    }
});
