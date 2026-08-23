<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\Alipay\Controller\Callback;
use Silksoftwarecorp\Alipay\Controller\Action;

class Notify extends Action
{

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRaw = $this->resultRawFactory->create();
        try{
            $result = $this->alipayClient->rsaCheckV1($params);
        }catch(\Exception $e){
          $this->logger->error($e->getMessage());
          $this->logger->error(json_encode($params));
            return $resultRaw->setContents('fail');
        }

        if($result){
            $outTradeNo = $this->getRequest()->getParam('out_trade_no');
            $tradeNo = $this->getRequest()->getParam('trade_no');
            $appId = $this->getRequest()->getParam('app_id');
            $totalAmount = $this->getRequest()->getParam('total_amount');

            if($appId != $this->helper->getPaymentConfig('app_id')){ //validate app id
                $this->logger->error("app_id doesn't match.");
                $this->logger->error(json_encode($params));
                return $resultRaw->setContents('fail');
            }

            $order = $this->getOrderByIncrementId($outTradeNo);
            if($order){//validate out_trade_no
                if($totalAmount != $this->helper->currencyConvert($order->getBaseGrandTotal(), $order->getBaseCurrencyCode(), 'CNY')){ //validate total amount
                    $this->logger->error("total_amount doesn't match.");
                    $this->logger->error(json_encode($params));
                    return $resultRaw->setContents('fail');
                }

                if($params['trade_status'] == "TRADE_SUCCESS" && $order->getStatus() == "pending"){
                    try{
                        $this->invoiceOrderByIncrementId($outTradeNo);

                      return $resultRaw->setContents('success');
                    }catch(\Exception $e){
                        $this->logger->error($e->getMessage());
                        $this->logger->error(json_encode($params));
                        return $resultRaw->setContents('fail');
                    }
                }

                return $resultRaw->setContents('success');
            }else{
                $this->logger->error("Order IncrementId: ".$outTradeNo." does not exist.");
                $this->logger->error(json_encode($params));
                return $resultRaw->setContents('fail');
            }

            return $resultRaw->setContents('success');
        }else{
            $this->logger->error("Notify RSA Check Fail.");
            $this->logger->error(json_encode($params));
            return $resultRaw->setContents('fail');
        }
    }



}
