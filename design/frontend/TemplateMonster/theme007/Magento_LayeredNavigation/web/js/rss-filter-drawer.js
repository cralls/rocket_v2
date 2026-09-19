define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (config, element) {
        var $drawer = $(element),
            $filter = $drawer.closest('.rss-category-filter'),
            eventNamespace = '.rssCategoryFilter';

        if ($drawer.data('rss-filter-drawer-initialized')) {
            return;
        }

        $drawer.data('rss-filter-drawer-initialized', true);

        modal({
            type: 'slide',
            title: $.mage.__('Filter Products'),
            modalClass: 'rss-category-filter-modal',
            trigger: '[data-trigger="rss-filter"]',
            responsive: true,
            innerScroll: true,
            clickableOverlay: true,
            buttons: []
        }, $drawer);

        function closeDropdowns() {
            $filter.find('[data-rss-filter-toggle]')
                .attr('aria-expanded', 'false');
            $filter.find('[data-rss-filter-dropdown]')
                .attr('hidden', 'hidden')
                .removeClass('is-open')
                .css('left', '');
        }

        $filter.on('click' + eventNamespace, '[data-rss-filter-toggle]', function (event) {
            var $button = $(this),
                dropdownId = $button.attr('aria-controls'),
                $dropdown = $filter.find('#' + dropdownId),
                shouldOpen = $button.attr('aria-expanded') !== 'true',
                filterOffset,
                buttonOffset,
                dropdownWidth,
                dropdownLeft;

            event.preventDefault();
            event.stopPropagation();
            closeDropdowns();

            if (!shouldOpen || !$dropdown.length) {
                return;
            }

            $dropdown.removeAttr('hidden').addClass('is-open');
            filterOffset = $filter.offset().left;
            buttonOffset = $button.offset().left;
            dropdownWidth = $dropdown.outerWidth();
            dropdownLeft = Math.max(14, buttonOffset - filterOffset);
            dropdownLeft = Math.min(dropdownLeft, $filter.outerWidth() - dropdownWidth - 14);

            $dropdown.css('left', Math.max(14, dropdownLeft));
            $button.attr('aria-expanded', 'true');
        });

        $filter.on('click' + eventNamespace, '[data-rss-filter-dropdown]', function (event) {
            event.stopPropagation();
        });

        $(document)
            .off('click' + eventNamespace)
            .on('click' + eventNamespace, closeDropdowns)
            .off('keydown' + eventNamespace)
            .on('keydown' + eventNamespace, function (event) {
                if (event.key === 'Escape') {
                    closeDropdowns();
                }
            });
    };
});
