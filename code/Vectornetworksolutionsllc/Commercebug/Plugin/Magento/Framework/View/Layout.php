<?php
namespace Vectornetworksolutionsllc\Commercebug\Plugin\Magento\Framework\View;
class Layout
{
    function beforeGenerateElements($subject){
        \Vectornetworksolutionsllc\Commercebug\Model\All::addTo(
            'request_layout_xml', $subject->getNode()->asXml());
    }
}
