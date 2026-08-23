<?php 
namespace VNS\Custom\Plugin;

class ContinueShoppingUrlPlugin
{
    protected $urlBuilder;
    protected $session;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->session = $session;
    }

    public function afterGetContinueShoppingUrl(
        \Magento\Checkout\Block\Cart $subject,
        $result
    ) {
        $teamPortalCategory = $this->session->getData('team_portal');
        //error_log("Checkout Team portal is ".$teamPortalCategory."\r\n", 3, '/home/'.get_current_user().'/public_html/error_log');
        
        if ($teamPortalCategory) {
            // Generate the URL for the category page
            return $this->urlBuilder->getUrl('catalog/category/view', ['id' => $teamPortalCategory]);
        }
        return $result;
    }
}
