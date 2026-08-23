<?php

namespace MageArray\OrderAttachments\Controller\Index;

use MageArray\OrderAttachments\Model\FileAttachmentFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Checkoutupload extends Action
{
    private $resultJsonFactory;

    /**
     * Checkoutupload constructor.
     * @param Context $context
     * @param Cart $cart
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Cart $cart,
        JsonFactory $resultJsonFactory,
        FileAttachmentFactory $fileAttachmentFactory,
        \MageArray\OrderAttachments\Helper\Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->fileAttachmentFactory = $fileAttachmentFactory;
        $this->dataHelper = $helper;

        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        try {
            $result = $this->dataHelper->uploadFile();
            $cartQuote = $this->cart->getQuote();
            $fileattach = $this->fileAttachmentFactory->create();
            $quoteid = $cartQuote->getEntityId();
            $fileattach->setQuoteId($quoteid);
            $fileattach->setFileData(json_encode($result));
            $fileattach->save();
            $result['id'] = $fileattach->getId();
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
