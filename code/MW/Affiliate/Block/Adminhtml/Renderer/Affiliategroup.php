<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class Affiliategroup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupmemberFactory
     */
    protected $_groupmemberFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory,
        array $data = []
    ) {
        $this->_groupFactory = $groupFactory;
        $this->_groupmemberFactory = $groupmemberFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['customer_id'])) {
            return '';
        }

        $result = '';
        $collection = $this->_groupmemberFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $row['customer_id']);

        $groupId = '';
        foreach ($collection as $group) {
            $groupId = $group->getGroupId();
            break;
        }

        if ($groupId != '') {
            $result .= $this->_groupFactory->create()->load($groupId)->getGroupName();
        }

        return __($result);
    }
}
