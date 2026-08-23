<?php

namespace MW\Affiliate\Block\Customer\Account\Invitation;

use MW\Affiliate\Model\Statusinvitation;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Model\AffiliateinvitationFactory
     */
    protected $_invitationFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Model\AffiliateinvitationFactory $invitationFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Model\AffiliateinvitationFactory $invitationFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_invitationFactory = $invitationFactory;
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
            'customer_invitation_transaction_pager'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->getInvitationHistory());

        return $this;
    }

    /**
     * @return \MW\Affiliate\Model\ResourceModel\Affiliateinvitation\Collection
     */
    public function getInvitationHistory()
    {
        $customerId = (int) $this->getCustomer()->getId();
        $collection = $this->_invitationFactory->create()->getCollection()
            ->addFieldtoFilter('customer_id', $customerId)
            ->addFieldToFilter('status', ['in' =>
                [
                    Statusinvitation::CLICKLINK,
                    Statusinvitation::REGISTER,
                    Statusinvitation::SUBSCRIBE,
                    Statusinvitation::PURCHASE
                ]
            ])->setOrder('invitation_time', 'DESC');

        // Set data for display via frontend
        return $collection;
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
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->_customerSession->getCustomer();
    }

    /**
     * @param $status
     * @return string
     */
    public function getStatusText($status)
    {
        return Statusinvitation::getLabel($status);
    }

    /**
     * @return array
     */
    public function getInvitationReport()
    {
        $customerId = (int) $this->getCustomer()->getId();
        $collection = $this->_invitationFactory->create()->getCollection()
            ->setReportInvitation($customerId);

        $result = [
            'click' => 0,
            'register' => 0,
            'purchase' => 0,
            'subscribe' => 0
        ];

        foreach ($collection as $invitation) {
            $result['click']      = $invitation->getCountClickLinkSum();
            $result['register']  = $invitation->getCountRegisterSum();
            $result['purchase']  = $invitation->getCountPurchaseSum();
            $result['subscribe'] = $invitation->getCountSubscribeSum();
            break;
        }

        return $result;
    }
}
