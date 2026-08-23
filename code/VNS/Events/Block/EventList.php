<?php
namespace VNS\Events\Block;

use Magento\Framework\View\Element\Template;
use VNS\Events\Model\EventFactory;
use Magento\Framework\View\Element\Template\Context;

class EventList extends Template
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

    public function getEvents()
    {
        $collection = $this->eventFactory->create()->getCollection();
        $collection->addFieldToFilter('from_date', ['gteq' => date('Y-m-d')]);
        $collection->setOrder('from_date', 'ASC');
        return $collection;
    }
    
}
