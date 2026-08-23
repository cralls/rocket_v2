<?php
namespace VNS\Custom\Block;
class CustomRecap extends \Magento\Framework\View\Element\Template
{
        protected $_registry;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {        
        //$this->customerSession = $customerSession;
        //$this->session = $session;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
}
?>