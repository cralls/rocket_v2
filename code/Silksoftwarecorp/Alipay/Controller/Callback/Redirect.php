<?php

/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\Alipay\Controller\Callback;
use Silksoftwarecorp\Alipay\Controller\Action;

class Redirect extends Action
{

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $result = $this->alipayClient->rsaCheckV1($params);
        if($result){
            try{
                $out_trade_no = htmlspecialchars($params['out_trade_no']);
                $order = $this->getOrderByIncrementId($out_trade_no);
                if($order){
                    $this->invoiceOrderByIncrementId($out_trade_no);
                    $resultRedirect = $this->resultRedirectFactory->create();

                    $resultRedirect->setPath('alipay/checkout/success');
                    return $resultRedirect;
                }else{
                    $this->logger->error(__("Order #%s does not exist.", $out_trade_no));
                    $this->messageManager->addError(__("Payment Error"));
                }

            }catch(\Exception $e){
                $this->logger->error($e->getMessage());
                $this->messageManager->addError(__("Payment Error"));
            }

        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('alipay/checkout/pay');
        return $resultRedirect;

    }


}
