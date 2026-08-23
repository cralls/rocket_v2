<?php
namespace Averun\SizeChart\Controller\Member\Manage;

use Averun\SizeChart\Controller\Member\Manage;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\View\Result\Page;

class Index extends Manage
{
    /**
     * @return Forward|Page
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->getBlock('customer_member');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__('Size Chart Members'));
        return $resultPage;
    }
}
