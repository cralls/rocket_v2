<?php
namespace VNS\Admin\Controller\Adminhtml\Orders;

class Statuses extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $statusCollection;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonConstructor;
        $this->statusCollection = $statusCollection;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $statuses = [];
        foreach ($this->statusCollection as $status) {
            $statuses[] = [
                'value' => $status->getStatus(),
                'label' => __($status->getLabel())
            ];
        }

        return $result->setData($statuses);
    }
}
