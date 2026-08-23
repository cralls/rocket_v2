<?php
namespace Rss\CustomDesigns\Block\Adminhtml\Request\Edit;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;

class Form extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return current Custom Design model
     */
    public function getModel()
    {
        return $this->registry->registry('rss_custom_design');
    }
}
