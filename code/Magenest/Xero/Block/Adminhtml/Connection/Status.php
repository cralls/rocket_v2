<?php
namespace Magenest\Xero\Block\Adminhtml\Connection;

use Magento\Backend\Block\Template;
use Magenest\Xero\Model\Helper;
use Magenest\Xero\Model\CoreConfig;
use Magenest\Xero\Helper\Signature;

class Status extends Template
{

    /**
     * Set Template
     *
     * @var string
     */
    protected $_template = 'system/config/connection/status.phtml';

    protected $_coreConfig;

    protected $_helper;

    public function __construct(
        Template\Context $context,
        CoreConfig $coreConfig,
        Helper $helper,
        array $data = []
    ) {
        $this->_coreConfig = $coreConfig;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check connection with Xero OAuth2
     *
     * @return int|null
     */
    public function isConnectedOA2()
    {
        if ($scopeId = $this->_request->getParam('website')) {
            $this->_helper->setScope('websites');
            $this->_helper->setScopeId($scopeId);
        }
        return $this->_coreConfig->getConfigValueByScope(
            Signature::PATH_XERO_OAUTH_V2_IS_CONNECTED,
            $this->_helper->getScope(),
            $this->_helper->getScopeId()
        );
    }
}
