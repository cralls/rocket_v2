<?php
namespace Averun\SizeChart\Controller\Member\Manage;

use Averun\SizeChart\Controller\Member\Manage;
use Averun\SizeChart\Model\Member;
use Averun\SizeChart\Model\MemberMeasure;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;

class FormPost extends Manage
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        $redirectUrl = null;
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        if (!$this->getRequest()->isPost()) {
            $this->getSession()->setMemberFormData($this->getRequest()->getPostValue());
            return $this->resultRedirectFactory->create()->setUrl(
                $this->_redirect->error($this->_buildUrl('*/*/edit'))
            );
        }
        $customer = $this->getSession()->getCustomer();
        try {
            if (empty($this->getRequest()->getParam('name'))) {
                throw new LocalizedException(__('Please enter the member name.'));
            }
            if ($this->getRequest()->getParam('active', false)) {
                $collection = $this->memberCollectionFactory->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('customer_id', $customer->getId());
                $collection->massUpdate(['active' => 0]);
            }
            /* @var $member Member */
            $member = $this->memberFactory->create();
            if (($memberId = (int) $this->getRequest()->getParam('id')) && !empty($memberId)) {
                $member->load($memberId);
            } else {
                $member->setCustomerId($customer->getId());
            }
            $member->setActive($this->getRequest()->getParam('active', false))
                ->setName($this->getRequest()->getParam('name'));
            $member->save();
            /* @var $measure MemberMeasure */
            $measure = $this->memberMeasureFactory->create();
            foreach ($this->getRequest()->getParam('dimension') as $dimensionId => $dimension) {
                if (empty($dimension)) {
                    continue;
                }
                $bindData = [
                    'customer_id'  => $customer->getId(),
                    'member_id'    => $member->getId(),
                    'dimension_id' => $dimensionId
                ];
                $measure->loadByFields($bindData);
                if (!$measure->getEntityId()) {
                    $measure->addData($bindData);
                }
                $measure->setData('value', $dimension);
                $measure->save();
            }
            $this->messageManager->addSuccessMessage(__('The member %1 has been saved.', htmlspecialchars($member->getName())));
            $redirectUrl = $this->_buildUrl('*/*/index', ['id' => $member->getId()]);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addErrorMessage(__('Cannot save member.'));
        }
        if (!empty($member) && $member->getId()) {
            $url = !empty($redirectUrl) ? $redirectUrl : $this->_buildUrl('*/*/edit', ['id' => $member->getId()]);
        } else {
            $url = !empty($redirectUrl) ? $redirectUrl : $this->_buildUrl('*/*/new');
        }
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
    }
}
