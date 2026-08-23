<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab;

class Actions extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    protected $productMetadata;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Actions $ruleActions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Actions $ruleActions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleActions = $ruleActions;
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

        $fieldset = $form->addFieldset(
            'action_fieldset',
            ['legend' => __('Calculate commission and discount as follows:')]
        );

        $fieldset->addField(
            'commission',
            'text',
            [
                'label'    => __('Affiliate Commission (if referred customer meets program conditions)'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'commission',
                'note'     => __('Format x or y% (x - fixed commission amount/ y% - percent of product value) separate multi-level marketing with commas for each level (ex 10%, 5%, 1 etc.)')
            ]
        );
        $fieldset->addField(
            'discount',
            'text',
            [
                'label'    => __('Referred Customer Discount for Subsequent Purchases'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'discount',
                'note'     => __("To change discounts (fixed amount 'x' or percentage 'y%') for subsequent customer orders, separate discounts with commas in field. Last figure will apply for all subsequent purchases. Ex 10%,5,0 for 10% discount on 1st purchase, $5 discount on 2nd purchase and no discount on subsequent purchases (until expiration date set in Affiliate Group detail)")
            ]
        );


        $url = "sales_rule/promo_quote/newActionHtml/form/rule_actions_fieldset";
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.3.5', '>')) {
            $url = "sales_rule/promo_quote/newActionHtml/form_namespace/rule_actions_fieldset";
        }

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl($url)
        );

        $actionFieldset = $form->addFieldset(
            'actions_fieldset',
            ['legend' => __('Affiliate will earn commission on individual cart items if they meet the following conditions (leave blank for all items)')]
        )->setRenderer($renderer);

        $actionFieldset->addField(
            'actions',
            'text',
            [
                'name'     => 'actions',
                'label'    => __('Apply To'),
                'title'    => __('Apply To'),
                'required' => true
            ]
        )->setRule($model)->setRenderer($this->_ruleActions);

        $actionFieldset->addField(
            'note_rule_program',
            'label',
            [
                'after_element_html' => __('Note: To create entire shopping cart rule go to Conditions tab.')
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
        return __('Affiliate Commission/Customer Discount');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Affiliate Commission/Customer Discount');
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
