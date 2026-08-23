<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliategroup;

class Save extends \MW\Affiliate\Controller\Adminhtml\Affiliategroup
{
    /**
     * Save Affiliate group
     */
    public function execute()
    {
        if ($this->getRequest()->getPost()) {
            $data = $this->getRequest()->getParams();
            $groupId = $this->getRequest()->getParam('id');
            $model = $this->_groupFactory->create();

            try {
                $_members = $this->getRequest()->getParam('addmember');
                $members = $_members['group'];
                $_programs = $this->getRequest()->getParam('addprogram');
                $programs = $_programs['member'];

                // Re-set group name
                $groupCollection = $model->load($groupId);
                $groupCollection->setGroupName($data['group_name'])
                    ->setLimitDay($data['limit_day'])
                    ->setLimitOrder($data['limit_order'])
                    ->setLimitCommission($data['limit_commission'])
                    ->save();

                // Edit group
                if ($groupId != '') {
                    // Group has member
                    if (isset($members)) {
                        // Remove old member data in group to add new member data
                        $memberCollection = $this->_groupmemberFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('group_id', $groupId);

                        if (sizeof($memberCollection) > 0) {
                            foreach ($memberCollection as $member) {
                                $member->delete();
                            }
                        }

                        $this->saveGroupMember($members, $groupId);
                    }

                    // Group has program
                    if (isset($programs)) {
                        $programCollection = $this->_groupprogramFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('group_id', $groupId);

                        if (sizeof($programCollection) > 0) {
                            foreach ($programCollection as $program) {
                                $program->delete();
                            }
                        }

                        $this->saveGroupProgram($programs, $groupId);
                    }
                }

                // Add new group
                if ($groupId == '') {
                    $groupCollection = $this->_groupFactory->create()->getCollection()
                        ->setOrder('group_id', 'DESC');
                    foreach ($groupCollection as $group) {
                        $groupId = $group ->getGroupId();
                        break;
                    }
                    // Group has member
                    if (isset($members)) {
                        $this->saveGroupMember($members, $groupId);
                    }
                    // Group has program
                    if (isset($programs)) {
                        $this->saveGroupProgram($programs, $groupId);
                    }

                }

                // Set total member customer program
                $this->_dataHelper->setTotalMemberProgram();

                $this->messageManager->addSuccess(__('The group has successfully saved'));
                $this->_session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }

        $this->messageManager->addError(__('Unable to find group to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @param $members
     * @param $groupId
     */
    public function saveGroupMember($members, $groupId)
    {
        $memberIdss = explode("&", $members);

        foreach ($memberIdss as $memberIds) {
            $memberId = explode("=", $memberIds);

            if (isset($memberId[0]) && ($memberId[0] != 0)) {
                $memberData = [
                    'customer_id' => $memberId[0],
                    'group_id' => $groupId
                ];

                // Set new group data for member in group
                $groupMembersCollection = $this->_groupmemberFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('customer_id', $memberData['customer_id']);

                if (sizeof($groupMembersCollection) > 0) {
                    foreach ($groupMembersCollection as $groupMember) {
                        $groupMember->setGroupId($groupId)->save();
                    }
                } else {
                    $this->_groupmemberFactory->create()
                        ->setData($memberData)
                        ->save();
                }
            }
        }
    }

    /**
     * @param $programs
     * @param $groupId
     */
    public function saveGroupProgram($programs, $groupId)
    {
        $programIdss = explode("&", $programs);
        $programData = [];

        foreach ($programIdss as $programIds) {
            $programId = explode("=", $programIds);
            $programData['program_id'] = $programId[0];
            $programData['group_id'] = $groupId;

            if ($programData['program_id'] != 0) {
                $this->_groupprogramFactory->create()
                    ->setData($programData)
                    ->save();
            }
        }
    }
}
