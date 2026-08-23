<?php
namespace VNS\Events\Block;

use Magento\Framework\View\Element\Template;
use VNS\Events\Model\EventFactory;
use Magento\Framework\View\Element\Template\Context;

class EventView extends Template
{
    protected $eventFactory;

    public function __construct(
        Context $context,
        EventFactory $eventFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->eventFactory = $eventFactory;
    }

    public function getEvent()
    {
        $eventId = $this->getRequest()->getParam('id');
        return $this->eventFactory->create()->load($eventId);
    }
}
