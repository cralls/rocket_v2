<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

use MW\Affiliate\Model\Statusinvitation;

class Invitation extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \MW\Affiliate\Model\AffiliateinvitationFactory
     */
    protected $_invitationFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\Affiliate\Model\AffiliateinvitationFactory $invitationFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\Affiliate\Model\AffiliateinvitationFactory $invitationFactory,
        array $data = []
    ) {
        $this->_invitationFactory = $invitationFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_member_invitation');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Invitation History Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_invitationFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $this->getRequest()->getParam('id')])
            ->setOrder('invitation_time', 'DESC');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'invitation_id',
            [
                'header'    => __('ID'),
                'align'     => 'right',
                'width'     => '50px',
                'index'     => 'invitation_id'
            ]
        );
        $this->addColumn(
            'invitation_time',
            [
                'header'    => __('Invitation Time'),
                'type'      => 'datetime',
                'width'     => '150px',
                'index'     => 'invitation_time',
                'gmtoffset' => true,
                'default'   => ' ---- '
            ]
        );
        $this->addColumn(
            'email',
            [
                'header'    => __('Customer Email Address'),
                'align'     => 'left',
                'index'     => 'email'
            ]
        );
        $this->addColumn(
            'ip',
            [
                'header'    => __('Ip Address'),
                'align'     => 'left',
                'index'     => 'ip'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header'    => __('Status'),
                'align'     => 'left',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => Statusinvitation::getOptionArray()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('affiliate/affiliatemember/invitation', ['id' => $this->getRequest()->getParam('id')]);
    }
}
