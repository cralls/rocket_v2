<?php

namespace MW\Affiliate\Controller\Checkout;

class ReferralCodePost extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_helper;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \MW\Affiliate\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_helper = $helper;
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $referral_code = $this->getRequest()->getParam('code_value');
        if ($this->getRequest()->getParam('removeCode') == 1) {
            $this->_helper->getCheckoutSession()->unsetData('referral_code');
            $referral_code = '';
        }
        if ($referral_code != '') {
            $check = $this->_helper->checkReferralCodeCart($referral_code);
            if ($check == 0) {
                $this->messageManager->addError(__('The referral code is invalid.'));
                return $result->setData(['failed' => true]);
            }
        }

        try {
            if ($referral_code !='') {
                $this->_helper->getCheckoutSession()->setReferralCode($referral_code); // set session
                $this->messageManager->addSuccess(__('The referral code was applied successfully.'));
                $result->setData(['success' => true]);
            } else {
                $this->messageManager->addSuccess(__('The referral code has been cancelled successfully.'));
                $result->setData(['canceled' => true]);
            }

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Can not apply the referral code.'));
        }

        $quote =  $this->_helper->getCheckoutSession()->getQuote();
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->save($quote->collectTotals());

        /** @var  \Magento\Quote\Model\Quote $quote */
        /*
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $this->quoteRepository->save($quote->collectTotals());
        */

        return $result;
        /* return $this->_pageFactory->create(); */
    }
    protected function _goBack($backUrl = null)
    {
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true]);
        //return $this->_redirect('checkout/cart');
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_url->getUrl('checkout/cart'));
        return $resultRedirect;
    }
}
