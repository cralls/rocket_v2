<?php
namespace MageArray\OrderAttachments\Helper;

use MageArray\OrderAttachments\Helper\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email extends AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD_ADMIN = 'orderattachments/general/custom_email_template_admin';
    const XML_PATH_EMAIL_TEMPLATE_FIELD_CUSTOMER = 'orderattachments/general/custom_email_template_customer';
    const XML_PATH_ADD_ATTACHMENT = 'orderattachments/general/add_attachment';

    const customer = 'customer';
    const admin = 'admin';

    protected $_scopeConfig;

    protected $_storeManager;

    protected $inlineTranslation;

    protected $_transportBuilder;
    protected $messageManager;

    protected $tempId;

    /**
     * Email constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAddAttachment($store = null)
    {
        $storeConfig = $this->scopeConfig->getValue(
            self::XML_PATH_ADD_ATTACHMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $storeConfig;
    }

    /**
     * @return mixed
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @param $xmlPath
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue(
            $xmlPath,
            $this->getStore()->getStoreId()
        );
    }
    /**
     * @param $emailTemplateVariables
     * @param $senderInfo
     * @param $receiverInfo
     * @return $this
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo, $file)
    {
        try {
            $emailTemplate = $this->_transportBuilder
            ->setTemplateIdentifier($this->tempId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND ,
                    'store' => $this->_storeManager->getStore()->getId() ,
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
            if ($this->getAddAttachment() == 1) {
                foreach ($file as $fileData) {
                    $emailTemplate->addAttachment(
                        $fileData['contents'],
                        $fileData['name'],
                        \Zend_Mime::TYPE_OCTETSTREAM
                    );
                }
            }
        } catch (\Exception $e) {
            
            $this->messageManager->addException($e, __($e->getMessage()));
        }
        return $this;
    }

    public function notifyByEmailAttachment($emailTemplateVariables, $senderInfo, $receiverInfo, $file)
    {
        try {
            if ($emailTemplateVariables['update'] == self::customer) {
                $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_TEMPLATE_FIELD_ADMIN);
            } else {
                $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_TEMPLATE_FIELD_CUSTOMER);
            }
            $this->inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo,
                $file
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
        }
    }
}
