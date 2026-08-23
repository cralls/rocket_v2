<?php

namespace MW\Affiliate\Model\System\Config\Source;

class Defaultgroup extends \Magento\Framework\DataObject
{
    /**
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param array $data
     */
    public function __construct(
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        array $data = []
    ) {
        $this->_groupFactory = $groupFactory;
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            'value' => '',
            'label' => __('-- Please Select --')
        ];
        $affiliateGroups = $this->_groupFactory->create()->getCollection();
        foreach ($affiliateGroups as $group) {
            $options[] = [
                'value' => $group->getGroupId(),
                'label' => $group->getGroupName()
            ];
        }

        return $options;
    }
}
