<?php

namespace MW\Affiliate\Controller\Invitation;

class Index extends \MW\Affiliate\Controller\Invitation
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Invitations'));

        return $resultPage;
    }
}
