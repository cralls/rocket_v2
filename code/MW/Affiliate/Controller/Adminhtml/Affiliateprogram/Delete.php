<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateprogram;

class Delete extends \MW\Affiliate\Controller\Adminhtml\Affiliateprogram
{
    /**
     * Delete Affiliate program
     */
    public function execute()
    {
        $programId = $this->getRequest()->getParam('id');
        if ($programId) {
            try {
                $model = $this->_programFactory->create();
                $model->setId($programId)->delete();

                $groupPrograms = $this->_groupprogramFactory->create()->getCollection()
                    ->addFieldToFilter('program_id', $this->getRequest()->getParam('id'));
                foreach ($groupPrograms as $groupProgram) {
                    $groupProgram->delete();
                }

                $this->messageManager->addSuccess(__('The program has successfully deleted'));
                $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $programId]);
            }
        }

        $this->_redirect('*/*/');
    }
}
