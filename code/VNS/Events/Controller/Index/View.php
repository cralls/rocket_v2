<?php
namespace VNS\Events\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use VNS\Events\Model\EventFactory;

class View extends Action
{
    protected $resultPageFactory;
    protected $eventFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EventFactory $eventFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->eventFactory = $eventFactory;
    }

    public function execute()
    {
        $eventId = $this->getRequest()->getParam('id');
        $event = $this->eventFactory->create()->load($eventId);

        if (!$event->getId()) {
            $this->_redirect('*/*/index');
            return;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($event->getName());
        return $resultPage;
    }
}
