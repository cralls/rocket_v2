<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Controller\Callback;
use Silksoftwarecorp\WechatPay\Controller\Action;
use Silksoftwarecorp\WechatPay\Model\Api\Data\NotifyResults;
class Notify extends Action
{

    public function execute()
    {
        $resultXml = $this->notify->Handle($this->apiConfig, false);
        $resultRaw = $this->resultRawFactory->create();
        if($this->notify->GetReturn_code() == "SUCCESS"){
            $xml = file_get_contents("php://input");
            $result = NotifyResults::Init($this->apiConfig, $xml);
            if($result){
                $data = $result->GetValues();

                $outTradeNo = $data['out_trade_no'];
                $transactionId = $data['transaction_id'];
                $appId = $data['appid'];
                $merchantId = $data['mch_id'];
                $totalFee = $data['total_fee'];

                if($appId != $this->apiConfig->GetAppId()){ //validate app id
                    $this->logger->error("app_id doesn't match.");
                    $this->logger->error(json_encode($data));
                    $this->notify->SetReturn_code("FAIL");
                    $this->notify->SetReturn_msg("APPID does not match.");
                    $resultXml = $this->notify->ReplyNotify(false);
                    return $resultRaw->setContents($resultXml);
                }

                if($merchantId != $this->apiConfig->GetMerchantId()){ //validate app id
                    $this->logger->error("Merchant ID doesn't match.");
                    $this->logger->error(json_encode($data));
                    $this->notify->SetReturn_code("FAIL");
                    $this->notify->SetReturn_msg("Merchant ID doesn't match.");
                    $resultXml = $this->notify->ReplyNotify(false);
                    return $resultRaw->setContents($resultXml);
                }

                $order = $this->getOrderByIncrementId($outTradeNo);
                if($order){//validate out_trade_no
                    if($totalFee != $this->helper->formatTotal($order->getBaseGrandTotal(), $order->getBaseCurrencyCode())){ //validate total amount
                        $this->logger->error("Total Fee doesn't match.");
                        $this->logger->error(json_encode($data));
                        $this->notify->SetReturn_code("FAIL");
                        $this->notify->SetReturn_msg("Total Fee doesn't match.");
                        $resultXml = $this->notify->ReplyNotify(false);
                        return $resultRaw->setContents($resultXml);
                    }

                    if($data['result_code'] == "SUCCESS" && $order->getStatus() == "pending"){
                        try{
                            $payment = $order->getPayment();
                            $payment->setAdditionalInformation('transaction_id', $transactionId);
                            $payment->save();

                            $this->invoiceOrderByIncrementId($outTradeNo);
                            return $resultRaw->setContents($resultXml);
                        }catch(\Exception $e){
                            $this->logger->error($e->getMessage());
                            $this->notify->SetReturn_code("FAIL");
                            $this->notify->SetReturn_msg($e->getMessage());
                            $resultXml = $this->notify->ReplyNotify(false);
                            return $resultRaw->setContents($resultXml);
                        }
                    }
                }else{
                    $this->logger->error("Order IncrementId: ".$outTradeNo." does not exist.");
                    $this->notify->SetReturn_code("FAIL");
                    $this->notify->SetReturn_msg("Order IncrementId: ".$outTradeNo." does not exist.");
                    $resultXml = $this->notify->ReplyNotify(false);
                    return $resultRaw->setContents($resultXml);
                }

            }
        }
        return $resultRaw->setContents($resultXml);

    }

    protected function getOrderByIncrementId($incrementId){
        return $this->_orderFactory->create()->loadByIncrementId($incrementId);
    }


}
