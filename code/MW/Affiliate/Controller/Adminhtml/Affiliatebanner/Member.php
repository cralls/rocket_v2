<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

class Member extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('mw_affiliate_affiliatebanner.edit.tab.member');
        return $resultLayout;
    }
}
