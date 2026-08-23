<?php
namespace MageArray\OrderAttachments\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Upload extends Action
{
    private $resultJsonFactory;

    /**
     * Upload constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \MageArray\OrderAttachments\Helper\Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $helper;
        parent::__construct($context);
    }

    /**
    /**
     * @return mixed
     */
    public function execute()
    {
        $result = $this->dataHelper->uploadFile();
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
