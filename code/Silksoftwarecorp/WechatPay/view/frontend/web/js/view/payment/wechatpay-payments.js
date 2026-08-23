
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';
    rendererList.push(
        {
            type: 'wechat_pay',
            component: 'Silksoftwarecorp_WechatPay/js/view/payment/method-renderer/wechatpay'
        }
    );

    /**
     * Add view logic here if needed
     **/
    return Component.extend({});
});
