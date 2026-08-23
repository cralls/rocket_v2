<?php

namespace MageArray\OrderAttachments\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\Helper\Data;

class SetFileVariables implements ObserverInterface
{
    const EMAIL_SEND_SEPARATE = 2;
    const ENABLE_EMAIL_ATTACHMENT = 1;
    const ADD_ATTACHMENT = 1;

    protected $jsonHelper;

    /**
     * CheckoutSubmitAllAfter constructor.
     * @param \MageArray\OrderAttachments\Helper\Data $dataHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $jsonHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeInterface
     * @param  \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        \MageArray\OrderAttachments\Helper\Data $dataHelper,
        ScopeConfigInterface $scopeConfig,
        Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeInterface,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\App\State $state,
        \MageArray\OrderAttachments\Model\AttachmentsFactory $attachmentFactory
    ) {
        $this->_dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
        $this->quoteFactory = $quoteFactory;
        $this->storeInterface = $storeInterface;
        $this->_state = $state;
        $this->attachmentFactory = $attachmentFactory;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getEvent()->getTransport();
        if (is_array($transport)) {
            $transport = new \Magento\Framework\DataObject($transport);
        }
        $quoteId = $transport->getOrder()->getQuoteId();
        try {
            
            if ($this->_state->getAreaCode() == 'adminhtml') {
                $fileDatas = $this->getOrderFileData($transport->getOrder()->getId());
                $transport->setData('media_path', $this->_dataHelper->getMediaPath());
            } else {
                $fileDatas = $this->_dataHelper->fileData($quoteId);
                $transport->setData('media_path', $this->_dataHelper->getTempMediaPath());
            }
            $emailEnabled = $this->_dataHelper->isEmailEnabled();
            $emailSeparately = $this->_dataHelper->getEmailSaperately();
            $addAttachment = $this->_dataHelper->getAddAttachments();
            if (isset($fileDatas) && !empty($fileDatas) &&
                $emailSeparately == self::EMAIL_SEND_SEPARATE && $emailEnabled == self::ENABLE_EMAIL_ATTACHMENT &&
                $addAttachment == self::ADD_ATTACHMENT
            ) {
                $transport->setData('file_attach', 1);
                $transport->setData('file_data', $fileDatas);
            } else {
                $transport->setData('file_attach', 0);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __("Email didn't send because your SMTP has not been configured"));
        }
    }
    
    public function getOrderFileData($orderId)
    {
        $fileDatas = $this->_dataHelper->getAttachedFiles($orderId);
        $data = [];
        if (count($fileDatas) > 0) {
            foreach ($fileDatas as $attach) {
                $fileDetails['file_data'] = $this->jsonHelper->jsonEncode(["name" => $attach['file_name'],"file" => $attach['file_path']]);
                $data[] = $fileDetails;
            }
        }
        return $data;
    }
}
