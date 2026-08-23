<?php
namespace Averun\SizeChart\Controller;

use Averun\SizeChart\Model\MemberFactory;
use Averun\SizeChart\Model\MemberMeasureFactory;
use Averun\SizeChart\Model\ResourceModel\Member\CollectionFactory;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

abstract class Setter extends AbstractAccount
{
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var MemberMeasureFactory
     */
    protected $memberMeasureFactory;
    /**
     * @var MemberFactory
     */
    protected $memberFactory;
    /**
     * @var CollectionFactory
     */
    protected $memberCollectionFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        MemberMeasureFactory $memberMeasureFactory,
        MemberFactory $memberFactory,
        CollectionFactory $memberCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->memberMeasureFactory = $memberMeasureFactory;
        $this->memberFactory = $memberFactory;
        $this->memberCollectionFactory = $memberCollectionFactory;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object
     *
     * @return Session
     */
    protected function getSession()
    {
        return $this->customerSession;
    }
}
