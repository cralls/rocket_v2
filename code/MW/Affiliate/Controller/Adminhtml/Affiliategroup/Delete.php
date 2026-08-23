<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliategroup;

class Delete extends \MW\Affiliate\Controller\Adminhtml\Affiliategroup
{
    /**
     * Delete Affiliate group
     */
    public function execute()
    {
        $groupId = $this->getRequest()->getParam('id');

        if ($groupId) {
            $error = 0;
            $store = $this->_storeManager->getStore();
            $defaultGroup = (int) $this->_dataHelper->getDefaultGroupAffiliateStore($store->getCode());

            try {
                $groupMembers = $this->_groupmemberFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('group_id', $groupId);

                if (sizeof($groupMembers) > 0 || $groupId == 1 || $groupId == $defaultGroup) {
                    $error = 1;
                    if ($groupId == 1 || $groupId == $defaultGroup) {
                        $this->messageManager->addError(
                            __('Can not deleted the group with id = %1, which is an affiliate default group.', $groupId)
                        );
                    } else {
                        $this->messageManager->addError(
                            __('Can not deleted the group with id = %1, which contains active affiliate member.', $groupId)
                        );
                    }
                } else {
                    $model = $this->_groupFactory->create();
                    $model->setId($groupId)
                        ->delete();
                    $groupPrograms = $this->_groupprogramFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('group_id', $groupId);

                    foreach ($groupPrograms as $groupProgram) {
                        $groupProgram->delete();
                    }
                }

                // Set total member customer program
                $this->_dataHelper->setTotalMemberProgram();

                if ($error == 0) {
                    $this->messageManager->addSuccess(__('The group has successfully deleted'));
                }
                $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $groupId]);
            }
        }

        $this->_redirect('*/*/');
    }
}
