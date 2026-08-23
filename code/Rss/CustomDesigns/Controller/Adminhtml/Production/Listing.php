<?php
namespace Rss\CustomDesigns\Controller\Adminhtml\Production;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Listing extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::production_requests';

    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Rss_CustomDesigns::production_requests');
        $resultPage->getConfig()->getTitle()->prepend(__('Production Requests'));
        return $resultPage;
    }
}
