<?php 
namespace VNS\Custom\Plugin;

class ProductViewPlugin
{
    protected $categoryRepository;
    protected $session;
    protected $registry;

    public function __construct(
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Registry $registry
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->session = $session;
        $this->registry = $registry;
    }

    public function afterExecute(
        \Magento\Catalog\Controller\Product\View $subject,
        $result
    ) {
        // Retrieve the current product using the registry
        $product = $this->registry->registry('current_product');

        // Make sure there is a product and it has categories
        if ($product && $product->getCategoryIds()) {
            $categories = $product->getCategoryIds();
            foreach ($categories as $categoryId) {
                $category = $this->categoryRepository->get($categoryId);
                if ($category->getParentId() == 108) {
                    $this->session->setData('team_portal', $category->getId());
                    //error_log("Team portal is ".$this->session->getData('team_portal')." on SKU ".$product->getSku()."\r\n", 3, '/home/'.get_current_user().'/public_html/error_log');
                    break;
                }
            }
        }
        return $result;
    }
}
