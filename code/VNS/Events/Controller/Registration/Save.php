<?php
namespace VNS\Events\Controller\Registration;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class Save extends Action
{
    protected $formKeyValidator;
    protected $transportBuilder;
    protected $storeManager;

    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        /*$recaptchaResponse = $this->getRequest()->getParam('g-recaptcha-response');
        $secretKey = '6LdFPNscAAAAAFcXY9yA2ik0hLGtJRba4TWCyS8C';
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        
        $client = new \Zend_Http_Client($verifyUrl);
        $client->setParameterPost([
            'secret' => $secretKey,
            'response' => $recaptchaResponse
        ]);
        
        $response = json_decode($client->request(\Zend_Http_Client::POST)->getBody(), true);
        
        if (!$response['success']) {
            // Handle validation failure
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid reCAPTCHA. Please try again.'));
        }*/
        
        
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('*/*/index');
        }

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            // You can handle the form data here and send the email
            $this->sendEmail($data);
        }

        return $this->_redirect('*/*/success');
    }

    private function sendEmail($data)
    {
        $templateVars = ['data' => $data];
        $storeId = $this->storeManager->getStore()->getId();
        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $storeId,
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('47') // Replace with your custom email template ID
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom(['name'=>'Rocket Science Sports','email'=>'online.orders@rocketsciencesports.com']) // Replace with your custom email sender identity
            ->addTo('sales@rocketsciencesports.com') // Replace with your recipient email address
            //->addTo('cralls@vectorns.com')
            ->getTransport();

        $transport->sendMessage();
    }
}
