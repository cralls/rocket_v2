<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

class Save extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * Save Affiliate banner
     */
    public function execute()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $upload = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'image_name']
                )->validateFile();

            } catch (\Exception $e) {

            }

            $data = $this->getRequest()->getParams();
            $groupIds = "";
            if (isset($data["group_id"])) {
                $groupIds = implode(",", $data["group_id"]);
            }
            $data["group_id"] = $groupIds;

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

            if (isset($upload['name']) && $upload['name'] != '') {
                $fileName = '';

                try {
                    try {
                        $imageData = $upload;
                        $fileName = $this->_objectManager->get('MW\Affiliate\Helper\Import')
                            ->saveBannerImage($imageData);
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }

                // This way the name is saved in DB
                $data['image_name'] = 'mw_affiliate/'.$fileName;
            } else {
                if (isset($data['image_name']['delete']) && $data['image_name']['delete'] == 1) {
                    $data['image_name'] = '';
                } else {
                    unset($data['image_name']);
                }
            }

            $model = $this->_bannerFactory->create();
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();

                // Save member to banner
                $_members = $this->getRequest()->getParam('addmember');
                $members = $_members['banner'];
                if (isset($members)) {
                    // Delete old member data to update new member data
                    $memberCollection = $this->_bannermemberFactory->create()->getCollection()
                        ->addFieldToFilter('banner_id', $model->getId());
                    if (sizeof($memberCollection) > 0) {
                        foreach ($memberCollection as $member) {
                            $member->delete();
                        }
                    }
                    $this->saveBannerMember($members, $model->getId());
                }

                $this->messageManager->addSuccess(__('The banner has successfully saved'));
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

        $this->messageManager->addError(__('Unable to find banner to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @param $members
     * @param $bannerId
     */
    public function saveBannerMember($members, $bannerId)
    {
        $memberIdss = explode("&", $members);
        foreach ($memberIdss as $memberIds) {
            $memberId = explode("=", $memberIds);

            if (isset($memberId[0]) && ($memberId[0] != 0)) {
                $memberData = [
                    'customer_id' => $memberId[0],
                    'banner_id' => $bannerId
                ];
                $this->_bannermemberFactory->create()
                    ->setData($memberData)
                    ->save();
            }
        }
    }
}
