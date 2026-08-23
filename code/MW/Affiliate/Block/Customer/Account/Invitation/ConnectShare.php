<?php

namespace MW\Affiliate\Block\Customer\Account\Invitation;

// Load GmailOath library
if (!class_exists('GmailOath')) {
    require_once __DIR__ . '/../../../../lib/internal/api/gmail/GmailOath.php';
}
if (!class_exists('GmailGetContacts')) {
    require_once __DIR__ . '/../../../../lib/internal/api/gmail/GmailGetContacts.php';
}

class ConnectShare extends \Magento\Framework\View\Element\Template
{
    protected $_sClientId;

    protected $_sClientSecret;

    protected $_sCallback;

    protected $_iMaxResults;

    protected $_oAuth;

    protected $_oGetContacts;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
    }

    /**
     * @return $this
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->_customerSession->getCustomer();
    }

    /**
     * @return $this
     */
    protected function _init()
    {
        //$this->_sClientId         = '34760705102-aolcts0u7j60maegrc1uc5rqu7tlp149.apps.googleusercontent.com';
        //$this->_sClientSecret     = 'akoSuqdjOZzGtbXraPmh8QGJ';

        $this->_sClientId         = '164869874838.apps.googleusercontent.com';
        $this->_sClientSecret     = 'QFPpQSprjz7DB3K4PNQ746EA';
        $this->_sCallback         = $this->getUrl('affiliate/invitation/gmail');
        $this->_iMaxResults     = 2000; // Max results
        $this->_oAuth             = new \MW\Affiliate\lib\internal\api\gmail\GmailOath($this->_sClientId, $this->_sClientSecret, [], false, $this->_sCallback);
        $this->_oGetContacts     = new \MW\Affiliate\lib\internal\api\gmail\GmailGetContacts();
    }

    /**
     * @return string
     */
    public function getRequestToken()
    {
        return null;
        $oAuth = $this->_oAuth;
        $oGetContacts = $this->_oGetContacts;

        // Prepare access token and set it into session
        $oRequestToken = $oGetContacts->get_request_token($oAuth, false, true, true);

        $this->_session->setOauthToken($oRequestToken['oauth_token']);
        $this->_session->setOauthTokenSecret($oRequestToken['oauth_token_secret']);

        return $oAuth->rfc3986_decode($oRequestToken['oauth_token']);
    }

    /**
     * @return array|string
     */
    public function getContact()
    {
        $oAuth = $this->_oAuth;
        $oGetContacts = $this->_oGetContacts;

        $request = $this->getRequest();
        if ($request && $request->getParam('oauth_token')) {
            // Decode request token and secret
            $sDecodedToken = $oAuth->rfc3986_decode($request->getParam('oauth_token'));
            $sDecodedTokenSecret = $oAuth->rfc3986_decode($this->_session->getOauthTokenSecret());

            // Get 'oauth_verifier'
            $oAuthVerifier = $oAuth->rfc3986_decode($request->getParam('oauth_verifier'));

            // Prepare access token, decode it, and obtain contact list
            $oAccessToken = $oGetContacts->get_access_token(
                $oAuth,
                $sDecodedToken,
                $sDecodedTokenSecret,
                $oAuthVerifier,
                false,
                true,
                true
            );
            $sAccessToken = $oAuth->rfc3986_decode($oAccessToken['oauth_token']);
            $sAccessTokenSecret = $oAuth->rfc3986_decode($oAccessToken['oauth_token_secret']);
            $aContacts = $oGetContacts->GetContacts(
                $oAuth,
                $sAccessToken,
                $sAccessTokenSecret,
                false,
                true,
                $this->_iMaxResults
            );

            // Turn array with contacts into html string
            $response = [];
            foreach ($aContacts as $k => $aInfo) {
                $sContactName = end($aInfo['title']);
                $aLast = end($aContacts[$k]);

                foreach ($aLast as $aEmail) {
                    if ($aEmail['address']) {
                        $response[] = [
                            'name'     => $sContactName,
                            'email' => $aEmail['address']
                        ];
                    }
                }
            }

            return $response;
        }

        return '';
    }
}
