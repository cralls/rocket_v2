<?php
namespace MageArray\OrderAttachments\Model;

/**
 * Ecommerce Model
 *
 * @method \Jute\Ecommerce\Model\Resource\Page _getResource()
 * @method \Jute\Ecommerce\Model\Resource\Page getResource()
 */
class FileAttachment extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\MageArray\OrderAttachments\Model\ResourceModel\FileAttachment::Class);
    }
}
