<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silksoftware (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Silksoftwarecorp\WechatPay\Model\Method\WechatPay as PaymentMethod;

class WechatPayConfigProvider implements ConfigProviderInterface
{

    const PAYMENT_LOGO_UPLOAD_DIR = "wechatpay/logo/";

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $helper;

    /**
     * @param \Magento\Framework\View\Asset\Repository      $assetRepo
     * @param \Magento\Framework\App\RequestInterface       $request
     * @param \Magento\Store\Model\StoreManagerInterface    $storeManager
     * @param \Magento\Framework\UrlInterface               $urlBuilder
     * @param \Silksoftwarecorp\WechatPay\Helper\Data       $helper
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        \Silksoftwarecorp\WechatPay\Helper\Data $helper
    ) {
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {

        $config = [
            'payment' => [
                'wechatpay' => [
                    'payUrl' => $this->urlBuilder->getUrl(PaymentMethod::PAY_URL_PATH),
                    'logoUrl' => $this->getPaymentLogoUrl(),
                    'instructions' => $this->helper->getPaymentConfig('instructions')
                ]
            ]
        ];


        return $config;
    }

    public function getPaymentLogoUrl()
    {
        $logoValue = $this->helper->getPaymentConfig('logo');
        if (!empty($logoValue)) {
            $logoSrc =
                $this->storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . self::PAYMENT_LOGO_UPLOAD_DIR . $logoValue;
        } else {
            $logoSrc = $this->getViewFileUrl('Silksoftwarecorp_WechatPay::images/logo/default.png');
        }

        return $logoSrc;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    protected function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            return null;
        }
    }

}
