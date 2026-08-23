<?php
namespace Averun\SizeChart\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Member extends \Magento\Framework\Model\AbstractModel
{
    private $membersHolder = [];

    /**
     * @var Session
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        Registry $registry,
        Session $customerSession,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Averun\SizeChart\Model\ResourceModel\Member');
    }

    /**
     * @param null $customerId
     * @return AbstractDb
     */
    public function getCustomerMembers($customerId = null)
    {
        if (empty($customerId)) {
            $customerId = $this->customerSession->getCustomer()->getId();
        }
        if (!key_exists($customerId, $this->membersHolder)) {
            $members = $this->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder('name');
            $members->load();
            $this->membersHolder[$customerId] = $members;
        }
        return $this->membersHolder[$customerId];
    }

    public function loadByFields($bindFields)
    {
        $this->getResource()->loadByFields($this, $bindFields);
        return $this;
    }
}
