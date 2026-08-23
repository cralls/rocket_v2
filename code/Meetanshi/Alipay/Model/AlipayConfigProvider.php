<?php

namespace Meetanshi\Alipay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\Alipay\Helper\Data as AlipayHelper;

/**
 * Class AlipayConfigProvider
 * @package Meetanshi\Alipay\Model
 */
class AlipayConfigProvider implements ConfigProviderInterface
{
    /**
     * @var AlipayHelper
     */
    protected $config;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $methodCodes = ['alipay'];
    /**
     * @var array
     */
    protected $methods = [];

    /**
     * AlipayConfigProvider constructor.
     * @param AlipayHelper $config
     * @param PaymentHelper $paymentHelper
     * @param StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(AlipayHelper $config, PaymentHelper $paymentHelper, StoreManagerInterface $storeManager)
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $showLogo = $this->config->showLogo();
        $imageUrl = $this->config->getPaymentLogo();
        $redirectUrl = $this->storeManager->getStore()->getBaseUrl() . 'alipay/payment/redirect';
        $config['payment']['alipay_payment']['imageurl'] = ($showLogo) ? $imageUrl : '';
        $config['payment']['alipay_payment']['is_active'] = $this->config->isActive();
        $config['payment']['alipay_payment']['payment_instruction'] = trim($this->config->getPaymentInstructions());
        $config['payment']['alipay_payment']['redirect_url'] = $redirectUrl;

        return $config;
    }
}
