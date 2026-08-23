<?php

namespace MW\Affiliate\Block\Customer\Account\Affiliate\Program;

use MW\Affiliate\Model\Statusprogram;

class Listprogram extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliateprogramFactory
     */
    protected $_programFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupmemberFactory
     */
    protected $_groupmemberFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupprogramFactory
     */
    protected $_groupprogramFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliateprogramFactory $programFactory
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory
     * @param \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliateprogramFactory $programFactory,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory,
        \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_programFactory = $programFactory;
        $this->_groupFactory = $groupFactory;
        $this->_groupmemberFactory = $groupmemberFactory;
        $this->_groupprogramFactory = $groupprogramFactory;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Theme\Block\Html\Pager */
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'list_member_program_pager'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->getListProgram());

        return $this;
    }

    /**
     * @return \MW\Affiliate\Model\ResourceModel\Affiliategroupprogram\Collection
     */
    public function getListProgram()
    {
        $customerId = (int) $this->_customerSession->getCustomer()->getId();
        $programIds = [];
        $customerGroups = $this->_groupmemberFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId);

        if ($customerGroups->getSize() > 0) {
            $groupId = 0;
            foreach ($customerGroups as $group) {
                $groupId = $group->getGroupId();
                break;
            }

            $customerPrograms = $this->_groupprogramFactory->create()->getCollection()
                ->addFieldToFilter('group_id', $groupId);
            foreach ($customerPrograms as $program) {
                $programIds[] = $program->getProgramId();
            }
        }

        if (!$this->_storeManager->isSingleStoreMode()) {
            $programIds = $this->getProgramByStoreView($programIds);
        }

        $collection = $this->_programFactory->create()->getCollection()
            ->addFieldtoFilter('program_id', ['in' => $programIds])
            ->addFieldtoFilter('status', Statusprogram::ENABLED);

        // Set data for display via frontend
        return $collection;
    }

    /**
     * @param $programs
     * @return array
     */
    public function getProgramByStoreView($programs)
    {
        $programIds = [];
        $storeId = $this->_storeManager->getStore()->getId();

        foreach ($programs as $program) {
            $storeView = $this->_programFactory->create()->load($program)->getStoreView();
            $storeViews = explode(',', $storeView);
            if (in_array($storeId, $storeViews) || $storeViews[0] == '0') {
                $programIds[] = $program;
            }
        }

        return $programIds;
    }

    /**
     * Retrive collection from toolbar
     */
    public function getCollection()
    {
        return $this->getToolbar()->getCollection();
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getToolbar()->toHtml();
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        $customerId = $this->_customerSession->getCustomer()->getId();
        $groupName = '';

        $customerGroups = $this->_groupmemberFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        if ($customerGroups->getSize() > 0) {
            $groupId = 0;
            foreach ($customerGroups as $customerGroup) {
                $groupId = $customerGroup->getGroupId();
                break;
            }

            $group = $this->_groupFactory->create()->load($groupId);
            $groupName = $group->getGroupName();
        }

        return $groupName;
    }
}
