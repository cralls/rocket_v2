<?php

// app/code/VNS/Events/Controller/Adminhtml/Event/Delete.php

namespace VNS\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use VNS\Events\Model\EventFactory;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var EventFactory
     */
    private $eventFactory;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param EventFactory $eventFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        EventFactory $eventFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->eventFactory = $eventFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('event_id');
        if ($id) {
            try {
                $event = $this->eventFactory->create()->load($id);
                $event->delete();
                $this->messageManager->addSuccessMessage(__('The event has been deleted.'));
                $this->_redirect('events/event/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while deleting the event.'));
                $this->_redirect('events/event/index');
            }
        } else {
            $this->messageManager->addErrorMessage(__('Event not found.'));
            $this->_redirect('events/event/index');
        }
    }
}
