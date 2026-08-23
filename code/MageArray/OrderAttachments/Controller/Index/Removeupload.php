<?php

namespace MageArray\OrderAttachments\Controller\Index;

use MageArray\OrderAttachments\Model\FileAttachmentFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Removeupload extends Action
{
    private $resultJsonFactory;

    /**
     * Removeupload constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param FileAttachmentFactory $fileAttachmentFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        FileAttachmentFactory $fileAttachmentFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileAttachmentFactory = $fileAttachmentFactory;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        try {
            $attachmentid = $this->getRequest()->getParam('id', false);
            if ($attachmentid) {
                $this->fileAttachmentFactory->create()->setId($attachmentid)->delete();
            }
        } catch (\Exception $e) {
            $result = $this->resultJsonFactory->create()->setData([
                'status' =>0,
                'msg' => "Something went wrong. Please try again later."
            ]);
        }
        $result = $this->resultJsonFactory->create()->setData([
                'status' =>1,
                'msg' => "Attachment removed successfully."
            ]);
        return $result;
    }
}
