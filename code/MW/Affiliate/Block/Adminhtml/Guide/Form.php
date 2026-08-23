<?php

namespace MW\Affiliate\Block\Adminhtml\Guide;

/**
 * Class Form
 * @package MW\Affiliate\Block\Adminhtml\Guide
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form before rendering HTML.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('guide_');

        /*
         * General Instructions
         */
        $fieldset = $form->addFieldset(
            'general_fieldset',
            [
                'legend' => __('Affiliate - Template Integration Guide'),
                'class' => 'guide-fieldset',
            ]
        );

        $fieldset->addField(
            'general_instructions',
            'text',
            [
                'name' => 'general_instructions',
                'label' => __('Affiliate - Template Integration Guide'),
                'title' => __('Affiliate - Template Integration Guide'),
            ]
        )->setRenderer($this->getChildBlock('guide.general'));


        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
