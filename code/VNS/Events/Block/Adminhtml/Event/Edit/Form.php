<?php
namespace VNS\Events\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use VNS\Events\Model\EventFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Editor;
use Magento\Cms\Model\Wysiwyg\Config;




class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $storeManager;
    protected $eventFactory;
    
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        StoreManagerInterface $storeManager,
        EventFactory $eventFactory,
        Config $wysiwygConfig,
        array $data = []
        ) {
            $this->storeManager = $storeManager;
            $this->eventFactory = $eventFactory;
            $this->_wysiwygConfig = $wysiwygConfig;
            parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('event_form');
        $this->setTitle(__('Event Information'));
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_event');
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action' => $this->getData('action'),
                'method' => 'post',
            ],
        ]);
        $this->setForm($form); // Set the form in the block

        $form->setHtmlIdPrefix('wys_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Event Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getEventId()) {
            $fieldset->addField(
                'event_id',
                'hidden',
                ['name' => 'event_id']
            );
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Event Name'),
                'title' => __('Event Name'),
                'required' => true,
            ]
        );

        $imageField = $fieldset->addField(
            'image_url',
            'image',
            [
                'name' => 'image_url',
                'label' => __('Listing Image (325x325)'),
                'title' => __('Listing Image (325x325)'),
            ]
        );
        $this->setFileElementAttributes($imageField);
        
        $fieldset->addField(
            'image_url_two',
            'image',
            [
                'name' => 'image_url_two',
                'label' => __('Highlight Image (1920px width)'),
                'title' => __('Highlight Image (1920px width)'),
            ]
            );
        
        $fieldset->addField(
            'image_url_three',
            'image',
            [
                'name' => 'image_url_three',
                'label' => __('Third Image'),
                'title' => __('Third Image'),
            ]
            );
        
        $fieldset->addField(
            'video_url',
            'text',
            [
                'name' => 'video_url',
                'label' => __('Video URL'),
                'title' => __('Video URL'),
            ]
            );
        
        $fieldset->addField(
            'from_date',
            'date',
            [
                'name' => 'from_date',
                'label' => __('Event Start Date'),
                'title' => __('Event Start Date'),
                'date_format' => 'yyyy-MM-dd',
                'required' => true,
            ]
            );
        
        $fieldset->addField(
            'to_date',
            'date',
            [
                'name' => 'to_date',
                'label' => __('Event End Date'),
                'title' => __('Event End Date'),
                'date_format' => 'yyyy-MM-dd',
                'required' => true,
            ]
            );
        
        $fieldset->addField(
            'time',
            'text',
            [
                'name' => 'time',
                'label' => __('Event Time'),
                'title' => __('Event Time')
            ]
            );
        
        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Event Description'),
                'title' => __('Event Description'),
                'style' => 'height:10em',
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => true,
            ]
            );

        $fieldset->addField(
            'location',
            'text',
            [
                'name' => 'location',
                'label' => __('Event Location'),
                'title' => __('Event Location'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'age_range',
            'text',
            [
                'name' => 'age_range',
                'label' => __('Age Range'),
                'title' => __('Age Range'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'type',
            'text',
            [
                'name' => 'type',
                'label' => __('Event Type'),
                'title' => __('Event Type'),
                'required' => true,
            ]
        );
        
        $fieldset->addField(
            'event_link',
            'text',
            [
                'name' => 'event_link',
                'label' => __('Event Link'),
                'title' => __('Event Link'),
                'required' => false,
            ]
            );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    /**
     * Set additional attributes for the file input element.
     *
     * @param AbstractElement $element
     * @return $this
     */
    protected function setFileElementAttributes(AbstractElement $element)
    {
        // Add the accept attribute if you want to restrict the file types that can be uploaded
        $element->setAccept('image/*');
        
        // Add the max file size attribute if desired (in bytes)
        // $element->setMaxFileSize(2097152); // For 2MB maximum file size
        
        return $this;
    }
}
