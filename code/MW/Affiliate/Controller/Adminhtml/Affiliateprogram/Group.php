<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateprogram;

class Group extends \MW\Affiliate\Controller\Adminhtml\Affiliateprogram
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        //return $this->resultPageFactory->create();
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('mw_affiliate_affiliateprogram.edit.tab.group');
        return $resultLayout;
    }
}
