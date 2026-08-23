<?php

namespace Rss\CustomDesigns\Block\Adminhtml\Patterns;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;

class BomModal extends Template
{
    protected $formKey;

    public function __construct(
        Context $context,
        FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    public function getLoadUrl()
    {
        return $this->getUrl('customdesigns/patterns/loadBoms');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('customdesigns/patterns/saveBoms');
    }

    public function getFormKeyValue()
    {
        return $this->formKey->getFormKey();
    }
}