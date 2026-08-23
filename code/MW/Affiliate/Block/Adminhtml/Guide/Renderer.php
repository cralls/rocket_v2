<?php

/**
 * MW
 *
 * NOTICE OF LICENSE
 *
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MW
 * @package     MW_Affiliate
 */

namespace MW\Affiliate\Block\Adminhtml\Guide;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * @category MW
 * @package  MW_Affiliate
 * @module   Affiliate
 * @author   MW Developer
 */
class Renderer extends \Magento\Backend\Block\Template implements RendererInterface
{
    /**
     * Render form element as HTML.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }
}
