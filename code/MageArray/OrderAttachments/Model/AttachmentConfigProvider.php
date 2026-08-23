<?php
namespace MageArray\OrderAttachments\Model;

use MageArray\OrderAttachments\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

class AttachmentConfigProvider implements ConfigProviderInterface
{
    const SYSTEM_PATH_MODULE_ENABLE = 'enabled';
    const XML_PATH_SEND_EMAIL_ATTACHMENT_DESCRIPTION_BLOCK = 'terms_and_conditions';

    /**
     * AttachmentConfigProvider constructor.
     * @param UrlInterface $urlBuilder
     * @param Data $dataHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Data $dataHelper,
        \Magento\Cms\Model\BlockRepository $staticBlockRepository,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_dataHelper = $dataHelper;
        $this->staticBlockRepository = $staticBlockRepository;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $moduleEnabled = $this->_dataHelper
            ->getStoreConfig(self::SYSTEM_PATH_MODULE_ENABLE);
        $descBlock = $this->_dataHelper
            ->getStoreConfig(self::XML_PATH_SEND_EMAIL_ATTACHMENT_DESCRIPTION_BLOCK);
        $desc = "";
        if ($descBlock != 0 && $descBlock!== "") {
            $staticBlock = $this->staticBlockRepository->getById($descBlock);
            if ($staticBlock && $staticBlock->isActive()) {
                $desc = $this->filterProvider->getBlockFilter()->setStoreId($this->storeManager->getStore()->getId())->filter($staticBlock->getContent());
            }
        }
        return [
            'attachments' => [
                'uploadUrl' => $this->_urlBuilder
                    ->getUrl('orderattachments/index/checkoutupload'),
                'removeUrl' => $this->_urlBuilder
                    ->getUrl('orderattachments/index/removeupload'),
                'enabledModule' => $moduleEnabled,
                'allowedFile' => $this->_dataHelper->allowedFiles(),
                'maxFileSize' => $this->_dataHelper->maxFileSize(),
                'mediaPath' => $this->_dataHelper->getTempMediaPath(),
                'fileData' => $this->_dataHelper->fileData(),
                'descBlock' => $desc,
                'displayFileType' => $this->_dataHelper->displayFileTypes(),
                'displayMaxSize' => $this->_dataHelper->displayMaxFileSize()
            ]
        ];
    }
}
