<?php
namespace VNS\Events\Block\Adminhtml\Event;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    protected function _construct()
    {
        $this->_objectId = 'event_id';
        $this->_blockGroup = 'VNS_Events';
        $this->_controller = 'adminhtml_event';

        parent::_construct();

        if ($this->_isAllowedAction('VNS_Events::save')) {
            $this->buttonList->update('save', 'label', __('Save Event'));
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('VNS_Events::delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Event'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
