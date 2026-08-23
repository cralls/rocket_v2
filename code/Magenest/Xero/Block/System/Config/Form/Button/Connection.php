<?php
namespace Magenest\Xero\Block\System\Config\Form\Button;

use Magenest\Xero\Helper\Signature;
use Magenest\Xero\Model\Config;
use Magenest\Xero\Model\Helper;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;
use Magenest\Xero\Model\Config\Source\OauthCallback;

class Connection extends Field
{
    protected $_template = "system/config/connection/oauth20.phtml";

    protected $message = "";

    /**
     * @var Signature
     */
    protected $signature;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $oauthCallback;

    /**
     * Connection constructor.
     * @param Signature $signature
     * @param Context $context
     * @param Helper $helper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        OauthCallback $oauthCallback,
        Signature $signature,
        Context $context,
        Helper $helper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->oauthCallback = $oauthCallback;
        $this->signature = $signature;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getConnectUrl()
    {
        return $this->getUrl('xero/app/connect', ['website' => $this->getRequest()->getParam('website')]);
    }

    /**
     * Unset some non-related element parameters
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : "Connect Xero App";
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }

    public function isClientIdExist()
    {
        return !empty($this->_scopeConfig->getValue(Signature::PATH_CLIENT_ID))
            && !empty($this->_scopeConfig->getValue(Signature::PATH_CLIENT_SECRET));
    }

    public function getOAuthUrl()
    {
        $storeCode = "";

        if ($scopeId = $this->_request->getParam('website')) {
            if ($this->_scopeConfig->getValue('web/url/use_store')) {
                $website = $this->storeManager->getWebsite($scopeId);
                $group = $this->storeManager->getGroup($website->getDefaultGroupId());
                $store = $this->storeManager->getStore($group->getDefaultStoreId());
                $storeCode = $store->getCode()."/";
            }
            $state = $this->_scopeConfig->getValue(Signature::PATH_CLIENT_STATE, 'website', $scopeId);
            $clientId = $this->_scopeConfig->getValue(Signature::PATH_CLIENT_ID, 'website', $scopeId);
        } else {
            if ($this->_scopeConfig->getValue('web/url/use_store')) {
                $store = $this->storeManager->getDefaultStoreView();
                $storeCode = $store->getCode()."/";
            }
            $state = $this->_scopeConfig->getValue(Signature::PATH_CLIENT_STATE);
            $clientId = $this->_scopeConfig->getValue(Signature::PATH_CLIENT_ID);
        }
        $urlCallBack = $this->oauthCallback->getWebhookUri();
        $option = [
            'response_type' => 'code',
            'client_id'                => $clientId,
            'redirect_uri'             => $urlCallBack,
            'scope' => 'openid email profile offline_access accounting.settings accounting.transactions accounting.contacts accounting.journals.read accounting.reports.read accounting.attachments',
            'state' => $state
        ];
        $this->signature->setParamsOAuth($option);
        return $this->_scopeConfig->getValue(Signature::URL_XERO_OAUTH_AUTHORIZE).'?'.$this->signature->sign();
    }

    public function isValidForConnect()
    {
        if ($scopeId = $this->_request->getParam('website')) {
            $this->helper->setScopeId($scopeId);
            $this->helper->setScope('websites');

            $state = $this->helper->getConfig(Signature::PATH_CLIENT_STATE);
            if (strpos($state, 'websites') === 0) {
                if ($this->_scopeConfig->getValue(Config::XML_PATH_XERO_MULTIPLE_ENABLED)) {
                    $this->message = "Please change client ID first! Can't reuse client ID for multiple website!";
                    return $this->helper->isUniqueConnectedClientId(
                        $this->helper->getConfig(Signature::PATH_CLIENT_ID)
                    );
                } else {
                    $this->message = "Please enable multiple website setting first!";
                }
            }
            return false;
        }

        return true;
    }

    public function getErrorMessage()
    {
        return $this->message;
    }
}
