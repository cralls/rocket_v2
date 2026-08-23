<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            22Fv6pLw5IYDg+MOn5aY78aMiqEfw1HA1mzrJzRXK+Y=
 * Last Modified: 2016-04-14T15:37:57+00:00
 * File:          app/code/Xtento/ProductExport/Model/ResourceModel/Profile.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\ResourceModel;

class Profile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('xtento_productexport_profile', 'profile_id');
    }
}
