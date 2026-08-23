<?php
namespace Averun\SizeChart\Controller\Member\Manage;

use Averun\SizeChart\Controller\Member\Manage;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class Form extends Manage
{
    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sizechart/member_manage');
        }
        $resultPage->getConfig()->getTitle()->set(__('Size Chart Members'));
        return $resultPage;
    }
}
