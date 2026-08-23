<?php

namespace Meetanshi\Alipay\Controller\Payment;

use Meetanshi\Alipay\Controller\Payment as AlipayPayment;
use Meetanshi\Alipay\Helper\Data;
use Magento\Sales\Model\Order;

/**
 * Class Redirect
 * @package Meetanshi\Alipay\Controller\Payment
 */
class Redirect extends AlipayPayment
{
    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $result = $this->jsonFactory->create();
            try {
                $order = $this->checkoutSession->getLastRealOrder();
                $order->setState(Order::STATE_PENDING_PAYMENT, true);
                $order->setStatus(Order::STATE_PENDING_PAYMENT);
                $order->save();

                $secretKey = $this->helper->genSecretKey();
                $payment = $order->getPayment();
                $html = $this->helper->getPaymentForm($order, $secretKey);
                $payment->setAdditionalInformation(Data::SECRET_KEY, $secretKey);
                $payment->setAdditionalInformation(Data::HTML_DATA, $html);
                $html = $order->getPayment()->getAdditionalInformation(Data::HTML_DATA);
                return $result->setData(['error' => false, 'success' => true, 'html' => $html]);
            } catch (\Exception $e) {
                return $result->setData(['error' => true, 'success' => false, 'message' => __('Payment exception')]);
            }
        }
        return false;
    }
}
