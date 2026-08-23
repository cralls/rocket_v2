<?php
namespace Magenest\Xero\Block\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field as ConfigFormField;
use \Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class AbstractButton
 *
 * @method string getButtonLabel()
 * @method string getHtmlId()
 * @method string getButtonUrl()
 */
class Button extends ConfigFormField
{

    /**
     * Sync Button Label
     *
     * @var string
     */
    protected $_buttonLabel = 'Sync Now';

    /**
     * @return $this|ConfigFormField
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/button.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    // phpcs:disable Generic.Files.LineLength.TooLong
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_buttonLabel;
        $router = !empty($originalData['button_url']) ? $originalData['button_url'] : '*/dashboard/index';
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'button_url' => $this->getUrl($router)
            ]
        );
        if ($buttonLabel != 'Get Xero Contact List') {
            $element->setComment('<strong style="color:red">Warning</strong>: Please save the configuration before syncing data');
        }

        return $this->_toHtml();
    }
}
