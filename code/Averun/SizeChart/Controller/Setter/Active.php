<?php
namespace Averun\SizeChart\Controller\Setter;

use Averun\SizeChart\Controller\Setter;
use Averun\SizeChart\Model\MemberFactory;
use Averun\SizeChart\Model\MemberMeasureFactory;
use Averun\SizeChart\Model\ResourceModel\Member\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Active extends Setter
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(Context $context, Session $customerSession, MemberMeasureFactory $memberMeasureFactory,
        MemberFactory $memberFactory, CollectionFactory $memberCollectionFactory, JsonFactory $resultJsonFactory)
    {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct(
            $context,
            $customerSession,
            $memberMeasureFactory,
            $memberFactory,
            $memberCollectionFactory
        );
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirect('sizechart/member_manage');
        }

        $memberId = (int)$this->getRequest()->getParam('member_id');
        $data["status"] = 'not save';
        if ($memberId) {
            $member = $this->memberFactory->create()->load($memberId);
            if ($member->getCustomerId() != $this->getSession()->getCustomerId()) {
                $data["status"] = 'fail';
            } else {
                try {
                    $collection = $this->memberCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_id', $this->getSession()->getCustomerId());
                    $collection->massUpdate(['active' => 0]);
                    $member->setData('active', 1);
                    $member->save();
                    $data["status"] = 'ok';
                } catch (\Exception $e) {
                    $data["status"] = 'fail';
                }
            }
        }

        return $this->resultJsonFactory->create()->setData($data);
    }
}
