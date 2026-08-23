/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    "mage/template",
    "mage/mage",
    "mage/calendar",
], function ($, $t,mageTemplate, alert) {
    'use strict';
    $.widget('mage.footerLinkJs', {
        _create: function () {
            var element = this.element;
            var self = this;
            // alert("hrere");
            var getUrl=self.options.IsEnable.url;
            $.ajax({
                url: getUrl,
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    $('#rma_guest_login').hide();
                    if(data.is_enable){
                        $('#rma_guest_login').show();
                    }
                }
            })
        },
    });
return $.mage.footerLinkJs;
});
