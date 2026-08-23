<?php
namespace VNS\Events\Block\Frontend\Registration;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
use VNS\Events\Model\EventFactory;

class Form extends Template
{
    protected $request;
    protected $eventFactory;
    
    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        EventFactory $eventFactory,
        array $data = []
        ) {
            parent::__construct($context, $data);
            $this->request = $request;
            $this->eventFactory = $eventFactory;
    }
    
    public function getEventId()
    {
        return (int) $this->request->getParam('event_id');
    }
    
    public function getEventName()
    {
        $eventId = $this->getEventId();
        if ($eventId) {
            try {
                $event = $this->eventFactory->create()->load($eventId);
                return $event->getName();
            } catch (\Exception $e) {
                // Handle the exception if needed.
            }
        }
        return ''; // Return an empty string if event not found.
    }
    
    public function getFormAction()
    {
        return $this->getUrl('events/registration/save');
    }
}
