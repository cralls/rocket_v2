<?php

namespace MW\Affiliate\Controller\Checkout;

class CreditPost extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_helper;
    protected $_creditHelper;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \MW\Affiliate\Helper\Data $helper,
        \MW\Affiliate\Helper\Data $creditHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_helper = $helper;
        $this->_creditHelper = $creditHelper;
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $customerSession = $this->_helper->getCustomerSession();
        /* check customer is login or not */
        if (!$customerSession->isLoggedIn()) {
            $this->messageManager->addError(__('You must login to use your Credit.'));
            return $result->setData(['failed' => true]);
        }

        /* check affiliate are disable or not */
        $customerId = $customerSession->getCustomer()->getId();
        if ($this->_helper->getLockAffiliate($customerId) >= 1) {
            $this->messageManager->addError(__('Affiliate Account was disabled, please contact us to solve this problem.'));
            return $result->setData(['failed' => true]);
        }

        /* canceled Credit value */
        $credit_value = $this->getRequest()->getParam('credit_value');
        if ($this->getRequest()->getParam('remove_credit') == 1) {
            $this->_helper->getCheckoutSession()->unsetData('credit_code');
            $this->_helper->getCheckoutSession()->setCredit(0); // set session
            $this->messageManager->addSuccess(__('The credit has cancelled successfully.'));
            return $result->setData(['canceled' => true]);
        }
        /*
          $quote = $this->_helper->getCheckoutSession()->getQuote();
          $address = $quote->isVirtual()?$quote->getBillingAddress():$quote->getShippingAddress();
          $subtotal = $address->getBaseSubtotal();
          $subtotal += $address->getBaseDiscountAmount() + $this->_helper->getCheckoutSession()->getCredit();
       */

        $maxAllowCredit = $this->_creditHelper->getMaxCreditToCheckOut();

        /* check if credit value is greater than grandTotal */
        $grandTotal = $this->_helper->getCheckoutSession()->getQuote()->getGrandTotal();
        if ($credit_value > $grandTotal) {
            $string = 'Your entered amount ( %s ) is greater than subtotal.';
            $string = sprintf($string, $this->_creditHelper->getPricingHelper()->currency($credit_value, true, false));
            $this->messageManager->addError(__($string));
            return $result->setData(['failed' => true]);
        }


        // max credit to checkout
        if ($maxAllowCredit < $credit_value && $maxAllowCredit != 0) {
            $string = 'Maximum amount of credit to checkout is "%s". Please insert a number that must be less than or equal "%s"';
            $string = sprintf(
                $string,
                $this->_creditHelper->getPricingHelper()->currency($maxAllowCredit, true, false),
                $maxAllowCredit
            );
            $this->messageManager->addError(__($string));
            return $result->setData(['failed' => true]);
        }

        /* check if customer credit is enought */
        $currentCreditOfCustomer = $this->_creditHelper->getCreditByCustomer($customerId);
        if ($credit_value > $currentCreditOfCustomer) {
            $string = 'Your balance is not enough "%s".';
            $string = sprintf(
                $string,
                $this->_creditHelper->getPricingHelper()->currency($credit_value, true, false)
            );
            $this->messageManager->addError(__($string));
            return $result->setData(['failed' => true]);
        }

        try {
            if ($credit_value !='') {
                $this->_helper->getCheckoutSession()->setCredit($credit_value); // set session
                $string = 'Credit "%s" was applied successfully.';
                $string = sprintf(
                    $string,
                    $this->_creditHelper->getPricingHelper()->currency($credit_value, true, false)
                );
                $this->messageManager->addSuccess(__($string));
                $result->setData(['success' => true]);
            } else {
                $this->messageManager->addSuccess(__('The credit has cancelled successfully.'));
                $result->setData(['canceled' => true]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Can not apply the referral code.'));
        }

        return $result;
    }
}
