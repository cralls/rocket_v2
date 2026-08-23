<?php

namespace Averun\SizeChart\Block\Adminhtml\Data;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;

class ImportExport extends Widget
{
    /**
     * @var string
     */
    protected $_template = 'importExport.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setUseContainer(true);
    }
}
