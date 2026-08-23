<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatemember_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Affiliate Information'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_member_detail',
            [
                'label' => __('General information'),
                'title' => __('General information'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Form'
                )->toHtml()
            ]
        );

        $affiliateData = $this->_coreRegistry->registry('affiliate_data_member');
        if ($affiliateData && $affiliateData->getId()) {
            $this->addTab(
                'form_member_credit',
                [
                    'label' => __('Manual Adjustment/Payout'),
                    'title' => __('Manual Adjustment/Payout'),
                    'content' => $this->getLayout()->createBlock(
                        'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Credit'
                    )->toHtml()
                ]
            );
            $this->addTab(
                'form_member_credit_history',
                [
                    'label' => __('Transaction History'),
                    'title' => __('Transaction History'),
                    'content' => $this->getLayout()->createBlock(
                        'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Credithistory'
                    )->toHtml()
                ]
            );
            $this->addTab(
                'form_member_invitation',
                [
                    'label' => __('Invitation History'),
                    'title' => __('Invitation History'),
                    'content' => $this->getLayout()->createBlock(
                        'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Invitation'
                    )->toHtml()
                ]
            );
            $this->addTab(
                'form_member_withdrawn',
                [
                    'label' => __('Withdrawal History'),
                    'title' => __('Withdrawal History'),
                    'content' => $this->getLayout()->createBlock(
                        'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Withdrawn'
                    )->toHtml()
                ]
            );

            $orderId = $this->getRequest()->getParam('orderid');
            if (isset($orderId)) {
                $content = $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Viewtransaction'
                )->toHtml();
            } else {
                $content = $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Transaction'
                )->toHtml();
            }
            $this->addTab(
                'form_member_transaction',
                [
                    'label' => __('Commission History'),
                    'title' => __('Commission History'),
                    'content' => $content,
                    'active' => !isset($orderId) ? false : true
                ]
            );

            $this->addTab(
                'form_member_program',
                [
                    'label' => __('Affiliate Programs'),
                    'title' => __('Affiliate Programs'),
                    'content' => $this->getLayout()->createBlock(
                        'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Program'
                    )->toHtml()
                ]
            );
            $this->addTab(
                'form_member_website',
                [
                    'label' => __('Affiliate Websites'),
                    'title' => __('Affiliate Websites'),
                    'content' => $this->getLayout()->createBlock(
                        'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Website'
                    )->toHtml()
                ]
            );
            $this->addTab(
                'form_member_network',
                [
                    'label' => __('Affiliate Network'),
                    'title' => __('Affiliate Network'),
                    'content' => $this->getLayout()->createBlock(
                        'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Network'
                    )->toHtml()
                ]
            );
        }

        return parent::_beforeToHtml();
    }
}
