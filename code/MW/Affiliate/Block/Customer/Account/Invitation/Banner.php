<?php

namespace MW\Affiliate\Block\Customer\Account\Invitation;

use Magento\Framework\App\Filesystem\DirectoryList;
use MW\Affiliate\Model\Statusprogram;

class Banner extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\Affiliate\Model\AffiliatebannerFactory
     */
    protected $_bannerFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupmemberFactory
     */
    protected $_groupmemberFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \MW\Affiliate\Model\AffiliatebannerFactory $bannerFactory
     * @param \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resource,
        \MW\Affiliate\Model\AffiliatebannerFactory $bannerFactory,
        \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_resource = $resource;
        $this->_storeManager = $context->getStoreManager();
        $this->_bannerFactory = $bannerFactory;
        $this->_groupmemberFactory = $groupmemberFactory;
    }

    /**
     * @return array
     */
    public function getInvitationBanners()
    {
        $bannerMemberTable = $this->_resource->getTableName('mw_affiliate_banner_member');
        $customerId = $this->getCustomer()->getId();
        $groupMemberId = $this->getGroupMemberId($customerId);
        $storeId = $this->_storeManager->getStore()->getId();
        $bannerData = [];

        $collection = $this->_bannerFactory->create()->getCollection();
        $collection->getSelect()->join(
            ['banner_member' => $bannerMemberTable],
            'main_table.banner_id = banner_member.banner_id',
            ['customer_id']
        );
        $collection->addFieldToFilter('main_table.status', ['eq' => Statusprogram::ENABLED])
            ->addFieldToFilter('banner_member.customer_id', ['eq' => $customerId]);

        if ($collection->getSize() > 0) {
            foreach ($collection as $banner) {
                $groupIds = explode(',', $banner->getGroupId());
                $storeViews = explode(',', $banner->getStoreView());
                if ((in_array($storeId, $storeViews) || $storeViews[0] == '0')
                    && (in_array($groupMemberId, $groupIds))
                ) {
                    $bannerData[] = $banner;
                }
            }
        }

        return $bannerData;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->_customerSession->getCustomer();
    }

    /**
     * @param $customerId
     * @return int
     */
    public function getGroupMemberId($customerId)
    {
        $groupMember = $this->_groupmemberFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();

        if ($groupMember) {
            return $groupMember->getGroupId();
        } else {
            return 0;
        }
    }

    /**
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(DirectoryList::MEDIA);
    }
}
