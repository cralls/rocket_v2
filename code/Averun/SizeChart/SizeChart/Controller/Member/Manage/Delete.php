<?php
namespace Averun\SizeChart\Controller\Member\Manage;

use Averun\SizeChart\Controller\Member\Manage;
use Averun\SizeChart\Model\Member;

class Delete extends Manage
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            /* @var $member Member */
            $member = $this->memberFactory->create();
            if (($memberId = (int) $this->getRequest()->getParam(static::PARAM_CRUD_ID)) && !empty($memberId)) {
                $member->load($memberId);
            }
            if ($member->getCustomerId() != $this->getSession()->getCustomerId()) {
                $this->messageManager->addErrorMessage(__('The member does not belong to this customer.'));
                return $resultRedirect->setPath('*/*/index');
            }
            $member->delete();
            $this->messageManager->addSuccessMessage(__('The member %1 has been deleted.', htmlspecialchars($member->getName())));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
