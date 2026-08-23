<?php

namespace MW\Affiliate\Controller\Invitation;

class Invite extends \MW\Affiliate\Controller\Invitation
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $customer_id = $this->_customerSession->getCustomer()->getId();
        $referral_code = $this->_affiliatecustomersFactory->create()->load($customer_id)
            ->getReferralCode();
        $url = $this->getRequest()->getPost('url_link');
        $post = $this->getRequest()->getPost('email');
        $message = $this->getRequest()->getPost('message');
        $post = trim($post, " ,");
        $emails = explode(',', $post);

        $validator = new \Zend_Validate_EmailAddress();
        $error = [];
        foreach ($emails as $email) {
            $name = $email;
            $_name = $this->getStringBetween($email, '"', '"');
            $_email = $this->getStringBetween($email, '<', '>');

            if ($_email!== false && $_name !== false) {
                $email = $_email;
                $name = $_name;
            } elseif ($_email!== false && $_name === false) {
                if (strpos($email, '"')===false) {
                    $email = $_email;
                    $name = $email;
                }
            }
            $email = trim($email);
            if ($validator->isValid($email)) {
                // Send email to friend
                $postObject = new \Magento\Framework\DataObject();
                $customer = $this->_customerSession->getCustomer();
                $postObject->setSender($customer);
                $postObject->setMessage($message);
                $postObject->setData('invitation_link', $url);
                $postObject->setData('referral_code', $referral_code);
                $this->_dataHelper->sendEmailToInvite($email, $name, $postObject);
            } else {
                $error[] = $email;
            }
        }
        if (sizeof($error)) {
            $err = implode("<br>", $error);
            $this->messageManager->addError(__("These emails are invalid, the invitation message will not be sent to %1 ", $err));
        }
        $msg = "Your email was sent success";
        if (sizeof($emails) >1) {
            $msg = "Your Emails were sent successfully";
        }
        if (sizeof($emails) > sizeof($error)) {
            $this->messageManager->addSuccess(__($msg));
        }

        $this->_redirect('affiliate/invitation/index');
        return;
    }
    protected function getStringBetween($string, $startStr, $endStr)
    {
        $startStrIndex = strpos($string, $startStr);
        if ($startStrIndex === false) {
            return false;
        }
        $startStrIndex ++;
        $endStrIndex = strpos($string, $endStr, $startStrIndex);
        if ($endStrIndex === false) {
            return false;
        }
        return substr($string, $startStrIndex, $endStrIndex-$startStrIndex);
    }
}
