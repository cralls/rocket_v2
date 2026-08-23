<?php

namespace MW\Affiliate\Controller\Index;

class Ajaxgetfee extends \MW\Affiliate\Controller\Index
{
    /**
     * Get fee of payment gateway
     */
    public function execute()
    {
        if ($this->getRequest()->getPost('ajax') == 'true') {
            $data = $this->getRequest()->getPost('gateway');
            $result = json_encode($this->_dataHelper->getFeePaymentGateway($data));
            $this->getResponse()->setBody($result);
        }
    }
}
