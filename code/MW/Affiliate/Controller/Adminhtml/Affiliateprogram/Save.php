<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateprogram;

use MW\Affiliate\Model\Statusactive;

class Save extends \MW\Affiliate\Controller\Adminhtml\Affiliateprogram
{
    /**
     * Save Affiliate program
     */
    public function execute()
    {
        if ($this->getRequest()->getPost()) {
            $data = $this->getRequest()->getParams();

            if (isset($data['store_view'])) {
                if ($this->_storeManager->isSingleStoreMode()) {
                    $data['store_view'] = '0';
                } else {
                    if (in_array('0', $data['store_view'])) {
                        $data['store_view'] = '0';
                    } else {
                        $data['store_view'] = implode(',', $data['store_view']);
                    }
                }
            }

            $programId = $this->getRequest()->getParam('id');
            $model = $this->_programFactory->create();
            $data['status'] = $data['status_program'];

            try {
                if ($programId != '') {
                    $model->setData($data)
                        ->setId($programId)
                        ->save();

                    // Save conditions
                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    if (isset($data['rule']['actions'])) {
                        $data['actions'] = $data['rule']['actions'];
                    }
                    $model->load($programId);
                    unset($data['rule']);
                    $model->loadPost($data);
                    $model->save();
                }

                if ($programId == '') {
                    $model->setData($data)->save();

                    // Save conditions
                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    if (isset($data['rule']['actions'])) {
                        $data['actions'] = $data['rule']['actions'];
                    }
                    unset($data['rule']);
                    $model->loadPost($data);
                    $model->save();
                    $programId = $model->getId();
                }

                // Process groups
                $_groups = $this->getRequest()->getParam('addgroup');
                $groups = $_groups ? $_groups['program']  : "";
                if (isset($groups)) {
                    // Remove old group data of this program
                    $collections = $this->_groupprogramFactory->create()->getCollection()
                        ->addFieldToFilter('program_id', $programId);
                    if (sizeof($collections) > 0) {
                        foreach ($collections as $collection) {
                            $collection->delete();
                        }
                    }

                    $groupIdss = explode("&", $groups);
                    $datagroup = [];
                    foreach ($groupIdss as $groupIds) {
                        $groupId = explode("=", $groupIds);
                        $datagroup['group_id'] = $groupId[0];
                        $datagroup['program_id'] = $programId;

                        if ($datagroup['group_id'] != 0) {
                            $this->_groupprogramFactory->create()
                                ->setData($datagroup)
                                ->save();
                        }
                    }
                }

                $groupProgram = [];
                $programCollection = $this->_groupprogramFactory->create()->getCollection()
                    ->addFieldToFilter('program_id', $programId);
                if (sizeof($programCollection) > 0) {
                    foreach ($programCollection as $program) {
                        $groupProgram[] = $program->getGroupId();
                    }
                }

                // Send notification email to customers when the new program is created
                if (isset($data['send_mail']) && $data['send_mail'] == 1) {
                    $affiliateCustomers = $this->_affiliatecustomersFactory->create()->getCollection()
                        ->addFieldToFilter('active', Statusactive::ACTIVE);

                    foreach ($affiliateCustomers as $affiliateCustomer) {
                        $customerId = $affiliateCustomer->getCustomerId();
                        $affiliateGroup = 0;
                        $customerPrograms = $this->_groupmemberFactory->create()->getCollection()
                            ->addFieldToFilter('customer_id', $customerId);
                        foreach ($customerPrograms as $program) {
                            $affiliateGroup = $program->getGroupId();
                        }

                        if (sizeof($groupProgram) > 0
                            && $affiliateGroup != 0
                            && in_array($affiliateGroup, $groupProgram)
                        ) {
                            $storeViews = explode(",", $model->getStoreView());
                            $stores = $this->_storeManager->getStores();
                            if ($storeViews[0] == '0') {
                                foreach ($stores as $store) {
                                    $this->_dataHelper->sendEmailNewProgram($data, $customerId, $store->getCode());
                                }
                            } else {
                                foreach ($storeViews as $storeId) {
                                    $store = $stores[$storeId];
                                    $this->_dataHelper->sendEmailNewProgram($data, $customerId, $store->getCode());
                                }
                            };
                        }
                    }
                }

                // Set total member customer program
                $this->_dataHelper->setTotalMemberProgram();

                $this->messageManager->addSuccess(__('The program has successfully saved'));
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

        $this->messageManager->addError(__('Unable to find program to save'));
        $this->_redirect('*/*/');
    }
}
