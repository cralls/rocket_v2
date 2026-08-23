define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal) {
    'use strict';

    return function (config) {
        var modalEl = $(config.modalSelector);
        var materialsEl = $(config.materialsSelector);
        var statusEl = $(config.statusSelector);
        var patternLabelEl = $(config.patternLabelSelector);
        var searchEl = $(config.searchSelector);
        var currentPatternId = null;
        var currentPatternNumber = '';

        if (!modalEl.length) {
            return;
        }

        modal({
            type: 'slide',
            title: $.mage.__('Edit BOMs'),
            modalClass: 'rss-pattern-bom-slideout',
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
                    text: $.mage.__('Save BOMs'),
                    class: 'action-primary',
                    click: function () {
                        saveBoms();
                    }
                }
            ]
        }, modalEl);

        function escapeHtml(value) {
            return $('<div/>').text(value === null || value === undefined ? '' : value).html();
        }

        function updateCount() {
            var checkedCount = materialsEl.find('.rss-bom-checkbox:checked').length;
            statusEl.text($.mage.__('Selected BOMs: ') + checkedCount);
        }

        function renderMaterials(materials, assignedIds) {
            var html = '';
            var assignedLookup = {};

            $.each(assignedIds || [], function (i, id) {
                assignedLookup[String(id)] = true;
            });

            if (!materials || !materials.length) {
                materialsEl.html('<div style="color:#999;">' + $.mage.__('No BOM materials found.') + '</div>');
                updateCount();
                return;
            }

            $.each(materials, function (index, material) {
                var materialId = String(material.material_id);
                var checked = assignedLookup[materialId] ? ' checked="checked"' : '';
                var code = material.material_code ? ' <span style="color:#666;">[' + escapeHtml(material.material_code) + ']</span>' : '';
                var type = material.material_type ? ' <span style="color:#999;">- ' + escapeHtml(material.material_type) + '</span>' : '';

                html += ''
                    + '<label class="rss-bom-row" style="display:block;padding:6px 0;border-bottom:1px solid #f0f0f0;">'
                    + '<input type="checkbox" class="rss-bom-checkbox" value="' + escapeHtml(materialId) + '"' + checked + ' style="margin-right:8px;" />'
                    + '<span class="rss-bom-row-text">'
                    + '<strong>' + escapeHtml(material.material_name) + '</strong>'
                    + code
                    + type
                    + '</span>'
                    + '</label>';
            });

            materialsEl.html(html);
            updateCount();
        }

        function filterRows() {
            var term = $.trim(searchEl.val()).toLowerCase();

            materialsEl.find('.rss-bom-row').each(function () {
                var row = $(this);
                var text = row.text().toLowerCase();

                if (!term || text.indexOf(term) !== -1) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }

        function loadBoms(patternId, patternNumber) {
            currentPatternId = patternId;
            currentPatternNumber = patternNumber || '';

            patternLabelEl.text($.mage.__('Pattern: ') + currentPatternNumber);
            statusEl.text($.mage.__('Loading...'));
            materialsEl.html('');
            searchEl.val('');

            $.ajax({
                url: config.loadUrl,
                type: 'GET',
                dataType: 'json',
                data: {
                    pattern_id: patternId
                }
            }).done(function (response) {
                if (!response || response.error) {
                    statusEl.text(response && response.message ? response.message : $.mage.__('Unable to load BOMs.'));
                    materialsEl.html('');
                    return;
                }

                patternLabelEl.text($.mage.__('Pattern: ') + (response.pattern_number || currentPatternNumber));
                renderMaterials(response.materials || [], response.assigned_ids || []);
            }).fail(function () {
                statusEl.text($.mage.__('Unable to load BOMs.'));
                materialsEl.html('');
            });
        }

        function saveBoms() {
            var selected = [];

            if (!currentPatternId) {
                return;
            }

            materialsEl.find('.rss-bom-checkbox:checked').each(function () {
                selected.push($(this).val());
            });

            statusEl.text($.mage.__('Saving...'));

            $.ajax({
                url: config.saveUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    form_key: config.formKey,
                    pattern_id: currentPatternId,
                    material_ids: selected
                }
            }).done(function (response) {
                if (!response || response.error) {
                    statusEl.text(response && response.message ? response.message : $.mage.__('Unable to save BOMs.'));
                    return;
                }

                window.location.reload();
            }).fail(function () {
                statusEl.text($.mage.__('Unable to save BOMs.'));
            });
        }

        $(document).on('click', config.triggerSelector, function (e) {
            e.preventDefault();
            e.stopPropagation();

            var el = $(this);
            var patternId = parseInt(el.attr('data-pattern-id'), 10) || 0;
            var patternNumber = el.attr('data-pattern-number') || '';

            if (!patternId) {
                return false;
            }

            modalEl.modal('openModal');
            loadBoms(patternId, patternNumber);

            return false;
        });

        $(document).on('change', modalEl.selector + ' .rss-bom-checkbox', function () {
            updateCount();
        });

        searchEl.on('keyup', function () {
            filterRows();
        });
    };
});