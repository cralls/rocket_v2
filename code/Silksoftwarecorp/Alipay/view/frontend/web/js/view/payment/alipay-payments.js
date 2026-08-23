
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';
    rendererList.push(
        {
            type: 'alipay',
            component: 'Silksoftwarecorp_Alipay/js/view/payment/method-renderer/alipay'
        }
    );

    /**
     * Add view logic here if needed
     **/
    return Component.extend({});
});
