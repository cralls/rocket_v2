<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            22Fv6pLw5IYDg+MOn5aY78aMiqEfw1HA1mzrJzRXK+Y=
 * Last Modified: 2021-01-15T20:37:30+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Log/Grid/Renderer/ResultMessage.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Log\Grid\Renderer;

class ResultMessage extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        return nl2br(parent::_getValue($row));
    }
}
