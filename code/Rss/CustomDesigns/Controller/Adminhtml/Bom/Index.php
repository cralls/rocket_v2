<?php

namespace Rss\CustomDesigns\Controller\Adminhtml\Bom;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::custom_designs';

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
        $resultPage->setActiveMenu('Rss_CustomDesigns::bom');
        $resultPage->getConfig()->getTitle()->prepend(__('BOM Materials'));

        return $resultPage;
    }
}