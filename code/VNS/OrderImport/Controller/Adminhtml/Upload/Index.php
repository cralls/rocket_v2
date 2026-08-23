<?php

namespace VNS\OrderImport\Controller\Adminhtml\Upload;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'VNS_OrderImport::upload'; // Ensure you have defined this ACL resource

    protected $resultPageFactory = false;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
        )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
        // Rendering logic or forwarding to a page with a form
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Orders'));
        
        return $resultPage;
    }
}
