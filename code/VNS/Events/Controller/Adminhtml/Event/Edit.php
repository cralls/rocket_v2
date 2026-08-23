<?php
namespace VNS\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use VNS\Events\Model\EventFactory;

class Edit extends Action
{
    protected $eventFactory;
    protected $coreRegistry;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        EventFactory $eventFactory
        ) {
            parent::__construct($context);
            $this->coreRegistry = $coreRegistry;
            $this->eventFactory = $eventFactory;
    }
    
    public function execute()
    {
        $eventId = $this->getRequest()->getParam('event_id');
        
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('VNS_Events::events');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Event'));
        
        if ($eventId) {
            try {
                $eventModel = $this->eventFactory->create()->load($eventId);
                if (!$eventModel->getId()) {
                    $this->messageManager->addErrorMessage(__('Event not found.'));
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    return $resultRedirect->setPath('*/*/');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while loading the event data.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $eventModel = $this->eventFactory->create();
        }
        
        $data = $this->_getSession()->getData('vns_events_event_data', true);
        if (!empty($data)) {
            $eventModel->setData($data);
        }
        
        $this->coreRegistry->register('current_event', $eventModel);
        
        return $resultPage;
    }
}
