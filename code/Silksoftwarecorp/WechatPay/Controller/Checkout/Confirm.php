<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Controller\Checkout;

use Silksoftwarecorp\WechatPay\Controller\Action;

class Confirm extends Action
{

    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        if($orderId = $this->getRequest()->getParam('id')){
            $order = $this->_orderFactory->create()->load($orderId);
            if($order->getStatus() == "processing"){
                return $this->resultRedirectFactory->create()->setPath('wechatpay/checkout/success');
            }
            $this->_coreRegistry->register('current_order', $this->_checkoutSession->getLastRealOrder());
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        }else{
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

    }
}
