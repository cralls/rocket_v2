<?php

namespace Meetanshi\Alipay\Controller\Payment;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Meetanshi\Alipay\Controller\Payment as AlipayPayment;
use Meetanshi\Alipay\Helper\Data as AlipayHelper;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Class Notify
 * @package Meetanshi\Alipay\Controller\Payment
 */
class Notify extends AlipayPayment implements CsrfAwareActionInterface
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $secretKey = $this->getRequest()->getParam(AlipayHelper::SECRET_KEY_NAME);
        $outTradeNo = $this->getRequest()->getParam('out_trade_no', '0');
        $arr = explode("_", $outTradeNo, 2);
        $orderId = $arr[0];
        if ((!$secretKey) && (!$outTradeNo)) {
            print_r("fail");
            return;
        }
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);

        $order->getPayment()->setAdditionalInformation(AlipayHelper::OUT_TRADE_NO, $outTradeNo);
        print_r("success");
        print_r("Success");
        $trade_no = $this->getRequest()->getParam('trade_no');
        $trade_status = $this->getRequest()->getParam('trade_status');
        $time = $this->getRequest()->getParam('notify_time');
        if ($trade_status == 'TRADE_FINISHED') {
            if ($this->helper->isAutoInvoice()) {
                if ($order->canInvoice()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                    $invoice->register();

                    $transaction = $this->transactionFactory->create();
                    $transaction->addObject($invoice)->addObject($invoice->getOrder())->save();

                    $this->invoiceSender->send($invoice);
                    $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))->setIsCustomerNotified(true);
                }
            }
            $historyComment = __('Alipay Payment Notification') . " - " . "TRADE_NO: '" . $trade_no . "', " . "STATUS: '" . $trade_status . "', " . "DATE: '" . $time;

            $order->addStatusHistoryComment($historyComment, Order::STATE_PROCESSING)->setIsCustomerNotified(true);
            $order->addStatusHistoryComment(__('The customer has successfully paid'), Order::STATE_PROCESSING)->setIsCustomerNotified(true);
            $order->save();

        } else {
            print_r("fail");
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
