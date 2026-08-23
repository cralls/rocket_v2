<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliategroup;

class Program extends \MW\Affiliate\Controller\Adminhtml\Affiliategroup
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('mw_affiliate_affiliategroup.edit.tab.program');
        return $resultLayout;
    }
}
