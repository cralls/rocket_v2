<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class Emailaffiliatemember extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['email'])) {
            return '';
        }

        $cutomer = $this->_customerFactory->create()->getCollection()
            ->addFieldToFilter('email', $row['email'])
            ->getFirstItem();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        $action     = $request->getActionName();
        if ($action == "exportCsv" || $action == "exportXml") {
            $result = $row['email'];
        } else {
            $result = __(
                "<b><a href=\"%1\">%2</a></b>",
                $this->getUrl("*/affiliatemember/edit", ['id' => $cutomer->getId()]),
                $row['email']
            );
        }

        return $result;
    }
}
