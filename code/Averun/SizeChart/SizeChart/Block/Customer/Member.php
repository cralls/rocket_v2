<?php
namespace Averun\SizeChart\Block\Customer;

use Averun\SizeChart\Model\Member as ModelMember;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Member extends Template
{
    private $membersCollection;
    /**
     * @var ModelMember
     */
    private $modelMember;

    public function __construct(
        Context $context,
        ModelMember $modelMember,
        array $data = []
    ) {
        $this->modelMember = $modelMember;
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('customer/member/grid.phtml');
        $this->membersCollection = $this->modelMember->getCustomerMembers();
    }

    public function getMembersCollection()
    {
        return $this->membersCollection;
    }

    public function getEditUrl($member)
    {
        return $this->getUrl('*/*/edit', ['id' => $member->getId()]);
    }

    public function getDeleteUrl($member)
    {
        return $this->getUrl('*/*/delete', ['id' => $member->getId()]);
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    public function getNewUrl()
    {
        return $this->getUrl('*/*/new');
    }
}
