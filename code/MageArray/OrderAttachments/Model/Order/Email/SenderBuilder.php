<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageArray\OrderAttachments\Model\Order\Email;

use MageArray\OrderAttachments\Helper\Template\TransportBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    protected $jsonHelper;

    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
        \MageArray\OrderAttachments\Helper\Data $dataHelper,
        Data $jsonHelper,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $transportBuilder
        );
        $this->_dataHelper = $dataHelper;
        $this->jsonHelper = $jsonHelper;
        $this->reader = $reader;
        $this->productMetadata = $productMetadata;
        $this->messageManager = $messageManager;
        $this->transportBuilderByStore = $transportBuilderByStore ?: ObjectManager::getInstance()->get(
            TransportBuilderByStore::class
        );
    }

    /**
     * Configure email template
     *
     * @return void
     */
    protected function configureEmailTemplate()
    {
        try {
            $this->transportBuilder
                ->setTemplateIdentifier($this->templateContainer->getTemplateId());
            $this->transportBuilder
                ->setTemplateOptions($this->templateContainer
                    ->getTemplateOptions());
            $this->transportBuilder
                ->setTemplateVars($this->templateContainer->getTemplateVars());
            if ($this->productMetadata->getVersion()< '2.2.4') {
                $this->transportBuilder->setFrom($this->identityContainer->getEmailIdentity());
            } else {
                $this->transportBuilderByStore->setFromByStore(
                    $this->identityContainer->getEmailIdentity(),
                    $this->identityContainer->getStore()->getId()
                );
            }
            $vars = $this->templateContainer->getTemplateVars();
            if (isset($vars['file_attach'])) {
                $mediaUrl = $vars['media_path'];

                if ($vars['file_attach'] == 1) {
                    $fileDatas = $vars['file_data'];

                    foreach ($fileDatas as $value) {
                        $fileData = $this->jsonHelper->jsonDecode($value['file_data']);

                        $fileName = $fileData['name'];
                        $filePath = $fileData['file'];

                        $file = $this->reader->fileGetContents($mediaUrl . $filePath);
                        $this->transportBuilder->addAttachment(
                            $file,
                            $fileName,
                            \Zend_Mime::TYPE_OCTETSTREAM
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
            $this->messageManager->addException($e, __("Email didn't send because your SMTP has not been configured"));
        }
    }
}
