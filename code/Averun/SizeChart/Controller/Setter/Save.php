<?php
namespace Averun\SizeChart\Controller\Setter;

use Averun\SizeChart\Controller\Setter;
use Averun\SizeChart\Model\MemberFactory;
use Averun\SizeChart\Model\MemberMeasure;
use Averun\SizeChart\Model\MemberMeasureFactory;
use Averun\SizeChart\Model\ResourceModel\Member\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends Setter
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

        $data["status"] = 'not save';
        $dimensionId = (int) $this->getRequest()->getParam('dimension_id');
        $dimensionValue = (float) $this->getRequest()->getParam('value');
        $memberId = (int) $this->getRequest()->getParam('member_id');
        $customer = $this->getSession()->getCustomer();
        if ($memberId && $dimensionId && $dimensionValue) {
            try {
                /** @var $measure MemberMeasure */
                $measure = $this->memberMeasureFactory->create();
                $bindData = [
                    'customer_id'  => $customer->getId(),
                    'member_id'    => $memberId,
                    'dimension_id' => $dimensionId
                ];
                $measure->loadByFields($bindData);
                if (!$measure->getEntityId()) {
                    $measure->addData($bindData);
                }
                $measure->setData('value', $dimensionValue);
                $measure->save();
                $data["status"] = 'ok';
            } catch (\Exception $e) {
                $data["status"] = 'fail';
            }
        }

        return $this->resultJsonFactory->create()->setData($data);
    }
}
