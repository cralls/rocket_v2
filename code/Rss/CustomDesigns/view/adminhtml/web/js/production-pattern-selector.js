define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal) {
    'use strict';

    return function (config) {
        var modalEl = $(config.modalSelector);
        var openButtonEl = $(config.openButtonSelector);
        var searchEl = $(config.searchSelector);
        var resultsEl = $(config.resultsSelector);
        var statusEl = $(config.statusSelector);
        var hiddenInputEl = $(config.hiddenInputSelector);
        var summaryEl = $(config.summarySelector);
        var timer = null;
        var loadUrl = $.trim(config.loadUrl || '');

        if (!modalEl.length) {
            return;
        }

        if (!loadUrl && openButtonEl.length) {
            loadUrl = $.trim(openButtonEl.attr('data-load-url') || '');
        }

        modal({
            type: 'slide',
            title: $.mage.__('Edit Pattern (SR) #s'),
            modalClass: 'rss-production-pattern-slideout',
            innerScroll: true,
            clickableOverlay: false,
            buttons: [
                {
                    text: $.mage.__('Cancel'),
                    class: 'action-secondary',
                    click: function () {
                        this.closeModal();
                    }
                },
                {
                    text: $.mage.__('Apply Selection'),
                    class: 'action-primary',
                    click: function () {
                        applySelection();
                        this.closeModal();
                    }
                }
            ]
        }, modalEl);

        function escapeHtml(value) {
            return $('<div/>').text(value === null || value === undefined ? '' : value).html();
        }

        function getSelectedIds() {
            var raw = $.trim(hiddenInputEl.val());
            if (!raw) {
                return [];
            }

            var ids = [];
            $.each(raw.split(','), function (index, value) {
                value = $.trim(value);
                if (value && $.inArray(value, ids) === -1) {
                    ids.push(value);
                }
            });

            return ids;
        }

        function renderResults(items) {
            var selectedLookup = {};
            var html = '';

            $.each(getSelectedIds(), function (index, id) {
                selectedLookup[String(id)] = true;
            });

            if (!items || !items.length) {
                resultsEl.html('<div style="color:#999;">' + $.mage.__('No patterns found.') + '</div>');
                updateCount();
                return;
            }

            $.each(items, function (index, item) {
                var patternId = String(item.pattern_id);
                var checked = selectedLookup[patternId] ? ' checked="checked"' : '';
                var desc = item.description ? ' <span style="color:#666;">- ' + escapeHtml(item.description) + '</span>' : '';

                html += ''
                    + '<label class="rss-production-pattern-row" style="display:block;padding:6px 0;border-bottom:1px solid #f0f0f0;">'
                    + '<input type="checkbox" class="rss-production-pattern-checkbox" value="' + escapeHtml(patternId) + '"' + checked + ' style="margin-right:8px;" />'
                    + '<span><strong>' + escapeHtml(item.pattern_number) + '</strong>' + desc + '</span>'
                    + '</label>';
            });

            resultsEl.html(html);
            updateCount();
        }

        function updateCount() {
            var count = resultsEl.find('.rss-production-pattern-checkbox:checked').length;
            statusEl.text($.mage.__('Selected Patterns: ') + count);
        }

        function renderSummary(items) {
            var html = '';

            if (!items || !items.length) {
                summaryEl.html('<div class="rss-no-patterns" style="color:#999;">' + $.mage.__('No patterns selected.') + '</div>');
                return;
            }

            $.each(items, function (index, item) {
                var desc = item.description ? ' <span style="color:#666;">- ' + escapeHtml(item.description) + '</span>' : '';
                html += '<div class="rss-selected-pattern-row" data-pattern-id="' + escapeHtml(item.pattern_id) + '" style="margin:0 0 6px 0;"><strong>' + escapeHtml(item.pattern_number) + '</strong>' + desc + '</div>';
            });

            summaryEl.html(html);
        }

        function loadPatterns(query) {
            if (!loadUrl) {
                statusEl.text($.mage.__('Pattern loader URL is empty.'));
                resultsEl.html('');
                return;
            }

            statusEl.text($.mage.__('Loading...'));
            resultsEl.html('');

            $.ajax({
                url: loadUrl,
                type: 'GET',
                dataType: 'json',
                cache: false,
                data: {
                    q: query || '',
                    selected_pattern_ids: hiddenInputEl.val() || '',
                    form_key: config.formKey || ''
                }
            }).done(function (response) {
                if (!response || response.error) {
                    statusEl.text(response && response.message ? response.message : $.mage.__('Unable to load patterns.'));
                    resultsEl.html('');
                    return;
                }

                renderResults(response.items || []);
            }).fail(function (xhr) {
                statusEl.text($.mage.__('Unable to load patterns.'));
                if (window.console) {
                    console.log('production-pattern-selector ajax failed');
                    console.log('loadUrl:', loadUrl);
                    console.log('status:', xhr.status);
                    console.log('response:', xhr.responseText);
                }
                resultsEl.html('');
            });
        }

        function applySelection() {
            var ids = [];
            var items = [];

            resultsEl.find('.rss-production-pattern-checkbox:checked').each(function () {
                var checkbox = $(this);
                var row = checkbox.closest('.rss-production-pattern-row');
                var strongText = $.trim(row.find('strong').text());
                var descText = $.trim(row.find('span').text()).replace(strongText, '').replace(/^\s*-\s*/, '');
                var patternId = $.trim(checkbox.val());

                if (patternId && $.inArray(patternId, ids) === -1) {
                    ids.push(patternId);
                    items.push({
                        pattern_id: patternId,
                        pattern_number: strongText,
                        description: descText
                    });
                }
            });

            hiddenInputEl.val(ids.join(','));
            renderSummary(items);
        }

        openButtonEl.on('click', function (e) {
            e.preventDefault();
            modalEl.modal('openModal');
            searchEl.val('');
            loadPatterns('');
        });

        $(document).on('change', '.rss-production-pattern-checkbox', function () {
            updateCount();
        });

        searchEl.on('keyup', function () {
            var value = $(this).val();

            clearTimeout(timer);
            timer = setTimeout(function () {
                loadPatterns(value);
            }, 250);
        });
    };
});
