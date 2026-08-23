<?php
namespace Rss\CustomDesigns\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;

class NewAction extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::custom_designs';
    
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Rss_CustomDesigns::custom_designs');
        $resultPage->getConfig()->getTitle()->prepend(__('New Custom Design Request'));
        return $resultPage;
    }
}
