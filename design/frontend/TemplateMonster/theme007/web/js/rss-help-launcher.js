/* RSS 2026-11-14: match the storefront help launcher to the homepage. */
(function (window) {
    'use strict';

    // Magento prints theme head scripts before the Zendesk snippet in HTML Head.
    var settings = window.zESettings = window.zESettings || {};
    var widget = settings.webWidget = settings.webWidget || {};
    var colors = widget.color = widget.color || {};

    colors.launcher = '#c6c9ca';
    colors.launcherText = '#000000';
}(window));
