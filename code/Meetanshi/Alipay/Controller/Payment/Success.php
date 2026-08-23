<?php

namespace Meetanshi\Alipay\Controller\Payment;

use Meetanshi\Alipay\Controller\Payment as AlipayPayment;
use Magento\Sales\Model\Order;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Class Success
 * @package Meetanshi\Alipay\Controller\Payment
 */
class Success extends AlipayPayment implements CsrfAwareActionInterface
{
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

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute()
    {
        $verify_result = $this->helper->verifyReturn();
        $params = $this->getRequest()->getParams();
        if ($verify_result) {
            if ($params['trade_status'] == 'TRADE_FINISHED' || $params['trade_status'] == 'TRADE_SUCCESS') {
                $outTradeNo = $params['out_trade_no'];
                $arr = explode("_", $outTradeNo, 2);
                $orderId = $arr[0];
                if (!$outTradeNo) {
                    $this->checkoutSession->restoreQuote();
                    $this->_redirect('checkout/cart');
                }
                $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                $payment = $order->getPayment();
                $tranid = random_int(1, 999999);
                $payment->setTransactionId($tranid);
                $payment->setLastTransId($tranid);
                $payment->setAdditionalInformation('sign', $params['sign']);
                $payment->setAdditionalInformation('trade_no', $params['trade_no']);
                $payment->setAdditionalInformation('sign_type', $params['sign_type']);
                $payment->setAdditionalInformation('out_trade_no', $params['out_trade_no']);
                $payment->setAdditionalInformation('trade_status', $params['trade_status']);
                $payment->setAdditionalInformation((array)$payment->getAdditionalInformation());

                $trans = $this->transactionBuilder;
                $transaction = $trans->setPayment($payment)->setOrder($order)->setTransactionId($tranid)->setAdditionalInformation((array)$payment->getAdditionalInformation())->setFailSafe(true)->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

                $payment->setParentTransactionId(null);

                $payment->save();
                $order->setState(Order::STATE_PROCESSING, true);
                $order->setStatus(Order::STATE_PROCESSING);

                $this->orderSender->notify($order);


                $order->addStatusHistoryComment(__('Transaction is approved by the bank'), Order::STATE_PROCESSING)->setIsCustomerNotified(true);

                $order->save();
                $transaction->save();

                $this->_redirect('checkout/onepage/success');
            } else {
                print_r("trade_status=" . $params['trade_status']);
            }
        } else {
            $this->checkoutSession->restoreQuote();
            $this->_redirect('checkout/cart');
        }
    }
}
