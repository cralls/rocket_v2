<?php
namespace VNS\Events\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use VNS\Events\Model\EventFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends Action
{
    protected $eventFactory;

    public function __construct(
        Context $context,
        EventFactory $eventFactory,
        UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $fileSystem
        ) {
            parent::__construct($context);
            $this->eventFactory = $eventFactory;
            $this->uploaderFactory = $uploaderFactory;
            $this->fileSystem = $fileSystem;
            $this->imagePath = 'wysiwyg/events'; // Add this line to define the image path
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $eventId = isset($data['event_id']) ? (int) $data['event_id'] : null;

            // Handle image_url, image_url2, and image_url3 fields
            $imageFields = ['image_url', 'image_url_two', 'image_url_three'];
            
            foreach ($imageFields as $imageField) {
                if (isset($_FILES[$imageField]['name']) && $_FILES[$imageField]['name'] != '') {
                    try {
                        $uploader = $this->uploaderFactory->create(['fileId' => $imageField]);
                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(true);
                        $path = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($this->imagePath);
                        $uploader->save($path);
                        $data[$imageField] = $this->imagePath . $uploader->getUploadedFileName();
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                        $this->_redirect('*/*/edit', ['event_id' => $eventId]);
                        return;
                    }
                } elseif (isset($data[$imageField]['delete'])) {
                    $data[$imageField] = null;
                } elseif (isset($data[$imageField]['value'])) {
                    $data[$imageField] = $data[$imageField]['value'];
                }
            }
            
            try {
                $eventModel = $this->eventFactory->create();
                if ($eventId) {
                    $eventModel->load($eventId);
                }

                $eventModel->setData($data);
                $eventModel->save();

                $this->messageManager->addSuccessMessage(__('Event has been saved.'));
                $this->_getSession()->setData('vns_events_event_data', false);

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', ['event_id' => $eventModel->getId(), '_current' => true]);
                }

                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while saving the event.'));
                $this->_getSession()->setData('vns_events_event_data', $data);
                return $this->_redirect('*/*/edit', ['event_id' => $eventId, '_current' => true]);
            }
        }

        return $this->_redirect('*/*/');
    }
}
