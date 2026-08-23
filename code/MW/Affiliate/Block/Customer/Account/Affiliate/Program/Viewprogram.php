<?php

namespace MW\Affiliate\Block\Customer\Account\Affiliate\Program;

class Viewprogram extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MW\Affiliate\Model\AffiliateprogramFactory
     */
    protected $_programFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MW\Affiliate\Model\AffiliateprogramFactory $programFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MW\Affiliate\Model\AffiliateprogramFactory $programFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_programFactory = $programFactory;
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
            'view_program_pager'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->getListProduct());

        return $this;
    }

    /**
     * @return \MW\Affiliate\Model\ResourceModel\Affiliateprogram\Collection
     */
    public function getListProduct()
    {
        $programId = $this->getRequest()->getParam('id');
        $collection = $this->_programFactory->create()->getCollection()
            ->addFieldtoFilter('program_id', $programId);

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
     * @return string
     */
    public function getProgramName()
    {
        return $program_name = $this->_programFactory->create()->load(
            $this->getRequest()->getParam('id')
        )->getProgramName();
    }
}
