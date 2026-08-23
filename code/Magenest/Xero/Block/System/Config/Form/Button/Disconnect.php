<?php
namespace Magenest\Xero\Block\System\Config\Form\Button;

use Magenest\Xero\Helper\Signature;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Disconnect extends Field
{
    /**
     * @var Signature
     */
    protected $signature;

    /**
     * @var string
     */
    protected $_template = "system/config/disconnect.phtml";

    /**
     * Connection constructor.
     * @param Signature $signature
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Signature $signature,
        Context $context,
        array $data = []
    ) {
        $this->signature = $signature;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getDisconnectUrl()
    {
        return $this->getUrl('xero/app/disconnect', ['website' => $this->getRequest()->getParam('website')]);
    }

    /**
     * Unset some non-related element parameters
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : "Disconnect Xero App";
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }
}
