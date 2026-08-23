<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateprogram;

class MassDelete extends \MW\Affiliate\Controller\Adminhtml\Affiliateprogram
{
    /**
     * Delete multi Affiliate programs
     */
    public function execute()
    {
        $programIds = $this->getRequest()->getParam('affiliateprogramGrid');

        if ($programIds) {
            try {
                foreach ($programIds as $programId) {
                    $program = $this->_programFactory->create()->load($programId);
                    $program->delete();

                    $groupPrograms = $this->_groupprogramFactory->create()->getCollection()
                        ->addFieldToFilter('program_id', $programId);
                    foreach ($groupPrograms as $groupProgram) {
                        $groupProgram->delete();
                    }
                }

                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully deleted', count($programIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }
}
