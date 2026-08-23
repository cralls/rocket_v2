<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliategroup;

class Member extends \MW\Affiliate\Controller\Adminhtml\Affiliategroup
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('mw_affiliate_affiliategroup.edit.tab.member');
        return $resultLayout;
    }
}
