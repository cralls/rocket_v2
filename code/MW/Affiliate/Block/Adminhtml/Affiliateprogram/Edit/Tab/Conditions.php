<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab;

class Conditions extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_ruleConditions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    protected $productMetadata;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $ruleConditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $ruleConditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleConditions = $ruleConditions;
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('affiliate_data_program');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $url = "sales_rule/promo_quote/newConditionHtml/form/rule_conditions_fieldset";
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.3.5', '>')) {
            $url = "sales_rule/promo_quote/newConditionHtml/form_namespace/rule_conditions_fieldset";
        }

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl($url)
        );

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Affiliate will earn commission on entire shopping cart if it meets the following conditions (leave blank for all products)')]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name'  => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions')
            ]
        )->setRule($model)->setRenderer($this->_ruleConditions);

        $fieldset->addField(
            'note_rule_program',
            'label',
            [
                'after_element_html' => __('Note: To create individual cart item rule go to Affiliate Commission/Customer Discount tab.')
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
