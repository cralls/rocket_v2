<?php

namespace MageArray\OrderAttachments\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\Helper\Data;

class CheckoutSubmitAllAfter implements ObserverInterface
{
    protected $jsonHelper;

    /**
     * CheckoutSubmitAllAfter constructor.
     * @param \MageArray\OrderAttachments\Helper\Data $dataHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $jsonHelper
     */
    public function __construct(
        \MageArray\OrderAttachments\Helper\Data $dataHelper,
        ScopeConfigInterface $scopeConfig,
        Data $jsonHelper,
        \MageArray\OrderAttachments\Helper\Email $emailHelper,
        \MageArray\OrderAttachments\Model\AttachmentsFactory $attachmentFactory,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
        $this->emailHelper = $emailHelper;
        $this->attachmentFactory = $attachmentFactory;
        $this->reader = $reader;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $quoteId = $quote->getData('entity_id');
        try {
            $fileDatas = $this->_dataHelper->fileData($quoteId);
            if (!empty($fileDatas)) {
                $attchmentId = [];
                foreach ($fileDatas as $value) {
                    $fileData = $this->jsonHelper->jsonDecode($value['file_data']);
                    $attchmentId[] = $this->saveData($fileData, $order);
                }

                $emailEnabled = $this->_dataHelper->isEmailEnabled();
                $fileArray = [];
                $mediaUrl = $this->_dataHelper->getMediaPath();
                foreach ($attchmentId as $key => $value) {
                    $attachment = $this->getAttachment($value);
                    $fileName = $attachment->getFileName();
                    $file = $this->reader->fileGetContents($mediaUrl . $attachment->getFilePath());
                    $fileArray[] = ['name'=>$fileName, 'contents'=> $file];
                }
                $emailSeparately = $this->_dataHelper->getEmailSaperately();
                if ($emailEnabled == 1 && $emailSeparately == 1) {
                    $this->_dataHelper->sendEmail($order->getId(), false, $fileArray, 1);
                }
                return $this;
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
        }
    }
    public function getAttachment($value)
    {
        $attachmentData = $this->attachmentFactory->create()->load($value);
        return $attachmentData;
    }

    public function saveData($fileData, $order)
    {
        $model = $this->attachmentFactory->create();
        $currenttime = time();
        $model->setData('created_at', $currenttime);
        $model->setData('updated_at', $currenttime);
        $model->setData('order_id', $order->getId());
        $model->setData('customer_id', $order->getCustomerId());
        $model->setData('file_name', $fileData['name']);
        $model->setData('file_path', $fileData['file']);
        $model->save();
        return $model->getId();
    }
}
