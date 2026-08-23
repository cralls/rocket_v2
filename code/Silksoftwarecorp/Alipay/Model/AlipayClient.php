<?php

/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\Alipay\Model;

class AlipayClient{

    const RESPONSE_SUFFIX = "_response";

	const ERROR_RESPONSE = "error_response";

	const SIGN_NODE_NAME = "sign";

	const ENCRYPT_XML_NODE_NAME = "response_encrypted";


    protected $gatewayUrl = "https://openapi.alipay.com/gateway.do";
    protected $gatewaySandboxUrl = "https://openapi.alipaydev.com/gateway.do";

    protected $encryptKey;
    protected $encryptType;
    protected $rsaPrivateKeyFilePath;

    protected $rsaPublicKeyFilePath;
    protected $apiVersion = "1.0";
    protected $format = "json";

    const REDIRECT_URL_PATH = "alipay/callback/redirect";
    const NOTIFY_URL_PATH = "alipay/callback/notify";

    protected $fileCharset = "UTF-8";
    protected $postCharset = "UTF-8";

    protected $sign;
    protected $signSourceData;

    protected $helper;
    protected $logger;

    /**
     *
     * @param \Silksoftwarecorp\Alipay\Helper\Data  $helper
     * @param \Silksoftwarecorp\Alipay\Model\Logger $logger
     */
    public function __construct(
        \Silksoftwarecorp\Alipay\Helper\Data $helper,
        \Silksoftwarecorp\Alipay\Model\Logger $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function getGatewayUrl(){
        if($this->helper->getPaymentConfig('sandbox')){
            $gatewayUrl = $this->gatewaySandboxUrl;
        }else{
            $gatewayUrl = $this->gatewayUrl;
        }
        return $gatewayUrl;
    }

    public function getPostCharset(){
        return $this->postCharset;
    }

    public function doRequest($request, $isPage=false){
        $url = $this->getGatewayUrl();
        if($isPage)
		{
			$result = $this->pageRequest($request,"post");
		}
		else
		{
			$result = $this->apiRequest($request);
		}

		return $result;
    }

    /**
     * get alipay.trade.page.pay form html
     * @param  \Magento\Sales\Model\Order\Payment $payment
     * @return string          form html string
     */
    public function alipayTradePagePay($payment){
        $order = $payment->getOrder();
        $amount = null;
        if($order->getBaseCurrencyCode() != 'CNY'){
            $amount = $this->helper->currencyConvert($order->getBaseGrandTotal(), $order->getBaseCurrencyCode(), 'CNY');
        }

        $request = new \Silksoftwarecorp\Alipay\Model\Request\AlipayTradePagePayRequest;
        $request->solveBizContent($payment, $amount);
        $request->setReturnUrl($this->helper->getUrl(self::REDIRECT_URL_PATH));
        $request->setNotifyUrl($this->helper->getUrl(self::NOTIFY_URL_PATH));
        $response = $this->doRequest($request, true);

        return $response;
    }

    /**
     * get alipay.trade.wap.pay form html
     * @param  \Magento\Sales\Model\Order\Payment $payment
     * @return string          form html string
     */
    public function alipayTradeWapPay($payment){
        $order = $payment->getOrder();
        $amount = null;
        if($order->getBaseCurrencyCode() != 'CNY'){
            $amount = $this->helper->currencyConvert($order->getBaseGrandTotal(), $order->getBaseCurrencyCode(), 'CNY');
        }

        $request = new \Silksoftwarecorp\Alipay\Model\Request\AlipayTradeWapPayRequest;
        $request->solveBizContent($payment, $amount);
        $request->setReturnUrl($this->helper->getUrl(self::REDIRECT_URL_PATH));
        $request->setNotifyUrl($this->helper->getUrl(self::NOTIFY_URL_PATH));
        $response = $this->doRequest($request, true);

        return $response;
    }

    /**
     * get alipay.trade.wap.pay form fields params
     * @param  \Magento\Sales\Model\Order\Payment $payment
     * @return array          form fields array
     */
    public function getAlipayTradeWapPayFormParams($payment){
        $order = $payment->getOrder();
        $amount = null;
        if($order->getBaseCurrencyCode() != 'CNY'){
            $amount = $this->helper->currencyConvert($order->getBaseGrandTotal(), $order->getBaseCurrencyCode(), 'CNY');
        }

        $request = new \Silksoftwarecorp\Alipay\Model\Request\AlipayTradeWapPayRequest;
        $request->solveBizContent($payment);
        $request->setReturnUrl($this->helper->getUrl(self::REDIRECT_URL_PATH));
        $request->setNotifyUrl($this->helper->getUrl(self::NOTIFY_URL_PATH));
        $params = $this->formatPayPageParams($request);
        return $params;
    }

    /**
     * get alipay.trade.page.pay form fields params
     * @param  \Magento\Sales\Model\Order\Payment $payment
     * @return array          form fields array
     */
    public function getAlipayTradePagePayFormParams($payment){
        $order = $payment->getOrder();
        $amount = null;
        if($order->getBaseCurrencyCode() != 'CNY'){
            $amount = $this->helper->currencyConvert($order->getBaseGrandTotal(), $order->getBaseCurrencyCode(), 'CNY');
        }

        $request = new \Silksoftwarecorp\Alipay\Model\Request\AlipayTradePagePayRequest;
        $request->solveBizContent($payment, $amount);
        $request->setReturnUrl($this->helper->getUrl(self::REDIRECT_URL_PATH));
        $request->setNotifyUrl($this->helper->getUrl(self::NOTIFY_URL_PATH));
        $params = $this->formatPayPageParams($request);
        return $params;
    }

    /**
     * call alipay.trade.refund
     * @param  \Magento\Sales\Model\Order\Payment $payment
     * @param  float|null $amount
     * @return boolean
     */
    public function alipayTradeRefund($payment, $amount=null){
        $order = $payment->getOrder();
        if($order->getBaseCurrencyCode() != 'CNY'){
            if($amount == null){
                $amount == $order->getBaseGrandTotal();
            }
            $amount = $this->helper->currencyConvert($amount, $order->getBaseCurrencyCode(), 'CNY');
        }

        $request = new \Silksoftwarecorp\Alipay\Model\Request\AlipayTradeRefundRequest;
        $request->solveBizContent($payment, $amount);
        $response = $this->doRequest($request);
        if($response){
            $response = $response->alipay_trade_refund_response;
            $resultCode = $response->code;
            if(!empty($resultCode) && $resultCode == 10000){
                return true;
            }else{
                $this->logger->error(json_encode($response));
                $this->logger->error($response->sub_code);
                $this->logger->error($response->sub_msg);
                throw new \Exception($response->sub_msg);
            }
        }

    }

    /**
     * format alipay.trade.page.pay alipay.trade.wap.pay params
     * @param  object $request
     * @return array
     */
    protected function formatPayPageParams($request){
        $this->setupCharsets($request);

		if (strcasecmp($this->fileCharset, $this->postCharset)) {
            $this->logger->error("local file charset is not same with post charset.");
			throw new \Exception("File Charset[" . $this->fileCharset . "] is not same with Post Charset[" . $this->postCharset . "]!");
		}

		$iv=null;

		if(!$this->checkEmpty($request->getApiVersion())){
			$iv=$request->getApiVersion();
		}else{
			$iv=$this->apiVersion;
		}
        //system params
        $sysParams = [];
        $sysParams['app_id'] = $this->helper->getPaymentConfig('app_id');
        $sysParams['method'] = $request->getApiMethodName();
        $sysParams['format'] = $this->format;
        if($request->getReturnUrl()){
            $sysParams['return_url'] = $request->getReturnUrl();
        }
        $sysParams['charset'] = $this->postCharset;
        $sysParams['sign_type'] = $this->helper->getPaymentConfig('sign_type');
        $sysParams['timestamp'] = $request->getTimestamp()?:date('Y-m-d H:i:s');
        $sysParams['version'] = $iv;
        if($request->getNotifyUrl()){
            $sysParams['notify_url'] = $request->getNotifyUrl();
        }


		//business params
		$apiParams = $request->getApiParas();

		if (method_exists($request,"getNeedEncrypt") &&$request->getNeedEncrypt()){

			$sysParams["encrypt_type"] = $this->encryptType;

			if ($this->checkEmpty($apiParams['biz_content'])) {

				throw new \Exception(" api request Fail! The reason : encrypt request is not supperted!");
			}

			if ($this->checkEmpty($this->encryptKey) || $this->checkEmpty($this->encryptType)) {

				throw new \Exception(" encryptType and encryptKey must not null! ");
			}

			if ("AES" != $this->encryptType) {

				throw new \Exception("encryptType only support AES");
			}

			//encrypt
			$enCryptContent = $this->encrypt($apiParams['biz_content'], $this->encryptKey);
			$apiParams['biz_content'] = $enCryptContent;

		}

		$totalParams = array_merge($apiParams, $sysParams);

		//pre-sign string
		$preSignStr = $this->getSignContent($totalParams);

		//sign
		$signType = $this->helper->getPaymentConfig('sign_type');
		$totalParams["sign"] = $this->generateSign($totalParams, $signType);
        return $totalParams;
    }

    /**
     * Page Request
     * @param  object $request
     * @param  string $httpmethod POST|GET
     * @return string             redirect url or form string
     */
	public function pageRequest($request,$httpmethod = "POST") {
        $gatewayUrl = $this->getGatewayUrl();

        $totalParams = $this->formatPayPageParams($request);

		if ("GET" == strtoupper($httpmethod)) {

			//urlencode
			$preString=$this->getSignContentUrlencode($totalParams);
			$requestUrl = $gatewayUrl."?".$preString;

			return $requestUrl;
		} else {
			return $this->buildRequestForm($totalParams);
		}


	}

    /**
     * Api Request
     * @param  object $request
     * @param  string|null $appInfoAuthtoken
     * @return object|boolean
     */
    public function apiRequest($request, $appInfoAuthtoken = null) {

        $this->setupCharsets($request);

		if (strcasecmp($this->fileCharset, $this->postCharset)) {
            $this->logger->error("local file charset is not same with post charset.");
			throw new \Exception("File Charset[" . $this->fileCharset . "] is not same with Post Charset[" . $this->postCharset . "]!");
		}

		$iv=null;

		if(!$this->checkEmpty($request->getApiVersion())){
			$iv=$request->getApiVersion();
		}else{
			$iv=$this->apiVersion;
		}
        //system params
        $sysParams = [];
        $sysParams['app_id'] = $this->helper->getPaymentConfig('app_id');
        $sysParams['method'] = $request->getApiMethodName();
        $sysParams['format'] = $this->format;
        $sysParams['charset'] = $this->postCharset;
        $sysParams['sign_type'] = $this->helper->getPaymentConfig('sign_type');
        $sysParams['timestamp'] = $request->getTimestamp()?:date('Y-m-d H:i:s');
        $sysParams['version'] = $iv;
        $sysParams["app_auth_token"] = $appInfoAuthtoken;


		//business params
		$apiParams = $request->getApiParas();

		if (method_exists($request,"getNeedEncrypt") &&$request->getNeedEncrypt()){

			$sysParams["encrypt_type"] = $this->encryptType;

			if ($this->checkEmpty($apiParams['biz_content'])) {

				throw new \Exception(" api request Fail! The reason : encrypt request is not supperted!");
			}

			if ($this->checkEmpty($this->encryptKey) || $this->checkEmpty($this->encryptType)) {

				throw new \Exception(" encryptType and encryptKey must not null! ");
			}

			if ("AES" != $this->encryptType) {

				throw new \Exception("encryptType only support AES");
			}

			//encrypt
			$enCryptContent = $this->encrypt($apiParams['biz_content'], $this->encryptKey);
			$apiParams['biz_content'] = $enCryptContent;

		}

		//sign
		$sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams), $this->helper->getPaymentConfig('sign_type'));

        $gatewayUrl = $this->getGatewayUrl();

		$requestUrl = $gatewayUrl . "?";
		foreach ($sysParams as $sysParamKey => $sysParamValue) {
			$requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, $this->postCharset)) . "&";
		}
		$requestUrl = substr($requestUrl, 0, -1);


		try {
			$resp = $this->curl($requestUrl, $apiParams);
            $this->logger->debug($requestUrl);
            $this->logger->debug(json_encode($apiParams));
		} catch (\Exception $e) {
            $this->logger->error($e->getMessage());
			return false;
		}


		$respWellFormed = false;



		$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);

        $signData = null;

		if ("json" == $this->format) {

			$respObject = json_decode($r);
			if (null !== $respObject) {
				$respWellFormed = true;
				$signData = $this->parserJSONSignData($request, $resp, $respObject);
			}
		} else if ("xml" == $this->format) {

			$respObject = @ simplexml_load_string($resp);
			if (false !== $respObject) {
				$respWellFormed = true;
				$signData = $this->parserXMLSignData($request, $resp);
			}
		}



		if (false === $respWellFormed) {
			$this->logger->error("HTTP_RESPONSE_NOT_WELL_FORMED");
            return false;
		}

		// check sign
		$this->checkResponseSign($request, $signData, $resp, $respObject);

		if (method_exists($request,"getNeedEncrypt") &&$request->getNeedEncrypt()){

			if ("json" == $this->format) {


				$resp = $this->encryptJSONSignSource($request, $resp);

				$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);
				$respObject = json_decode($r);
			}else{

				$resp = $this->encryptXMLSignSource($request, $resp);

				$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);
				$respObject = @ simplexml_load_string($r);

			}
		}

		return $respObject;
	}


    public function generateSign($params, $signType = "RSA") {
		return $this->sign($this->getSignContent($params), $signType);
	}

	public function rsaSign($params, $signType = "RSA") {
		return $this->sign($this->getSignContent($params), $signType);
	}

	public function getSignContent($params) {
		ksort($params);

		$stringToBeSigned = "";
		$i = 0;
		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

				$v = $this->characet($v, $this->postCharset);
				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . "$v";
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . "$v";
				}
				$i++;
			}
		}

		unset ($k, $v);
		return $stringToBeSigned;
	}


	public function getSignContentUrlencode($params) {
		ksort($params);

		$stringToBeSigned = "";
		$i = 0;
		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

				$v = $this->characet($v, $this->postCharset);

				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . urlencode($v);
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . urlencode($v);
				}
				$i++;
			}
		}

		unset ($k, $v);
		return $stringToBeSigned;
	}

	protected function sign($data, $signType = "RSA") {
		if($this->checkEmpty($this->rsaPrivateKeyFilePath)){
			$priKey = $this->helper->getPaymentConfig('app_private_key');
			$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
				wordwrap($priKey, 64, "\n", true) .
				"\n-----END RSA PRIVATE KEY-----";
		}else {
			$priKey = file_get_contents($this->rsaPrivateKeyFilePath);
			$res = openssl_get_privatekey($priKey);
		}

        if(!$res){
            throw new \Exception(__("Wrong private key format"));
        }

		if ("RSA2" == $signType) {
			openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		} else {
			openssl_sign($data, $sign, $res);
		}

		if(!$this->checkEmpty($this->rsaPrivateKeyFilePath)){
			openssl_free_key($res);
		}
		$sign = base64_encode($sign);
		return $sign;
	}



    /**
     * rsaCheckV1  rsa sign check
     * @param  array $params
     * @return boolean
     */
	public function rsaCheckV1($params) {
			$sign = $params['sign'];
			$params['sign_type'] = null;
			$params['sign'] = null;

            $signType = $this->helper->getPaymentConfig('sign_type');
			return $this->verify($this->getSignContent($params), $sign,$signType);
	}

    /**
     * rsaCheckV2  rsa sign check
     * @param  array $params
     * @return boolean
     */
	public function rsaCheckV2($params) {
		$sign = $params['sign'];
		$params['sign'] = null;

        $signType = $this->helper->getPaymentConfig('sign_type');
		return $this->verify($this->getSignContent($params), $sign, $signType);
	}

	function verify($data, $sign, $signType = 'RSA') {

		if($this->checkEmpty($this->rsaPublicKeyFilePath)){

			$pubKey= $this->helper->getPaymentConfig('alipay_public_key');
			$res = "-----BEGIN PUBLIC KEY-----\n" .
				wordwrap($pubKey, 64, "\n", true) .
				"\n-----END PUBLIC KEY-----";
		}else {
			$pubKey = file_get_contents($this->rsaPublicKeyFilePath);
			$res = openssl_get_publickey($pubKey);
		}
        if(!$res){
            throw new \Exception(__("Wrong Alipay Public Key"));
        }

		if ("RSA2" == $signType) {
			$result = openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
		} else {
			$result = openssl_verify($data, base64_decode($sign), $res);
		}

		if(!$this->checkEmpty($this->rsaPublicKeyFilePath)) {
			openssl_free_key($res);
		}

		return $result;
	}

    private function setupCharsets($request) {
		if ($this->checkEmpty($this->postCharset)) {
			$this->postCharset = 'UTF-8';
		}
        $appId = $this->helper->getPaymentConfig('app_id');
		$str = preg_match('/[\x80-\xff]/', $appId) ? $appId : print_r($request, true);
		$this->fileCharset = mb_detect_encoding($str, "UTF-8, GBK") == 'UTF-8' ? 'UTF-8' : 'GBK';
	}

    /**
     * [curl description]
     * @param  string $url
     * @param  array|null $postFields
     * @return string
     */
    protected function curl($url, $postFields = null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$postBodyString = "";
		$encodeArray = Array();
		$postMultipart = false;


		if (is_array($postFields) && 0 < count($postFields)) {

			foreach ($postFields as $k => $v) {
				if ("@" != substr($v, 0, 1))// upload file check
				{

					$postBodyString .= "$k=" . urlencode($this->characet($v, $this->postCharset)) . "&";
					$encodeArray[$k] = $this->characet($v, $this->postCharset);
				} else
				{
					$postMultipart = true;
					$encodeArray[$k] = new \CURLFile(substr($v, 1));
				}

			}
			unset ($k, $v);
			curl_setopt($ch, CURLOPT_POST, true);
			if ($postMultipart) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeArray);
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
			}
		}

		if ($postMultipart) {

			$headers = array('content-type: multipart/form-data;charset=' . $this->postCharset . ';boundary=' . $this->getMillisecond());
		} else {

			$headers = array('content-type: application/x-www-form-urlencoded;charset=' . $this->postCharset);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);




		$reponse = curl_exec($ch);

		if (curl_errno($ch)) {

			throw new \Exception(curl_error($ch), 0);
		} else {
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode) {
				throw new \Exception($reponse, $httpStatusCode);
			}
		}

		curl_close($ch);
		return $reponse;
	}

    /**
	 * encrype
	 * @param string $str
	 * @return string
	 */
	 function encrypt($str,$screct_key){

		$screct_key = base64_decode($screct_key);
		$str = trim($str);
		//$str = addPKCS7Padding($str);
		//$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC),1);
		//$encrypt_str =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC);
		$iv = substr($screct_key, 0, 16);
		$encrypt_str = openssl_encrypt($str, 'AES-256-CBC', $screct_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
		return base64_encode($encrypt_str);

	}

	/**
	 * decrypt
	 * @param string $str
	 * @return string
	 */
	 function decrypt($str,$screct_key){
		$str = base64_decode($str);
		$screct_key = base64_decode($screct_key);
		//$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC),1);
		//$encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC);
		$iv = substr($screct_key, 0, 16);
		$encrypt_str = openssl_decrypt($str, 'AES-256-CBC', $screct_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
		$encrypt_str = trim($encrypt_str);
		//$encrypt_str = stripPKSC7Padding($encrypt_str);
		return $encrypt_str;

	}

	/**
	 *
	 * @param string $source
	 * @return string
	 */
	function addPKCS7Padding($source){
		$source = trim($source);
		//$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$block = $source ;
		$pad = $block - (strlen($source) % $block);
		if ($pad <= $block) {
			$char = chr($pad);
			$source .= str_repeat($char, $pad);
		}
		return $source;
	}
	/**
	 *
	 * @param string $source
	 * @return string
	 */
	function stripPKSC7Padding($source){
		$source = trim($source);
		$char = substr($source, -1);
		$num = ord($char);
		if($num==62)return $source;
		$source = substr($source,0,-$num);
		return $source;
	}

    /**
	 *
	 * @param $data
	 * @param $targetCharset
	 * @return string
	 */
	function characet($data, $targetCharset) {

		if (!empty($data)) {
			$fileType = $this->fileCharset;
			if (strcasecmp($fileType, $targetCharset) != 0) {
				$data = mb_convert_encoding($data, $targetCharset, $fileType);
				//				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
			}
		}


		return $data;
	}


    /**
     * check value empty
     * @param  string|null $value
     * @return boolean
     */
	protected function checkEmpty($value) {
		if (!isset($value))
			return true;
		if ($value === null)
			return true;
		if (trim($value) === "")
			return true;

		return false;
	}

    /**
     * build form html
     * @param $para_temp
     * @return string
     */
	protected function buildRequestForm($para_temp) {
        $gatewayUrl = $this->getGatewayUrl();

		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$gatewayUrl."?charset=".trim($this->postCharset)."' method='POST'>";
		while (list ($key, $val) = each ($para_temp)) {
			if (false === $this->checkEmpty($val)) {
				$val = str_replace("'","&apos;",$val);
				$sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
			}
        }

		//submit button should not contain attribute "name"
        $sHtml = $sHtml."<input type='submit' value='ok' style='display:none;''></form>";

		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";

		return $sHtml;
	}

    function parserResponseSubCode($request, $responseContent, $respObject, $format) {

		if ("json" == $format) {

			$apiName = $request->getApiMethodName();
			$rootNodeName = str_replace(".", "_", $apiName) . self::RESPONSE_SUFFIX;
			$errorNodeName = self::ERROR_RESPONSE;

			$rootIndex = strpos($responseContent, $rootNodeName);
			$errorIndex = strpos($responseContent, $errorNodeName);

			if ($rootIndex > 0) {
				$rInnerObject = $respObject->$rootNodeName;
			} elseif ($errorIndex > 0) {

				$rInnerObject = $respObject->$errorNodeName;
			} else {
				return null;
			}

			if (isset($rInnerObject->sub_code)) {

				return $rInnerObject->sub_code;
			} else {

				return null;
			}


		} elseif ("xml" == $format) {

			return $respObject->sub_code;

		}


	}

	function parserJSONSignData($request, $responseContent, $responseJSON) {

		$signData = new \Silksoftwarecorp\Alipay\Model\SignData;

		$signData->sign = $this->parserJSONSign($responseJSON);
		$signData->signSourceData = $this->parserJSONSignSource($request, $responseContent);


		return $signData;

	}

	function parserJSONSignSource($request, $responseContent) {

		$apiName = $request->getApiMethodName();
		$rootNodeName = str_replace(".", "_", $apiName) . self::RESPONSE_SUFFIX;

		$rootIndex = strpos($responseContent, $rootNodeName);
		$errorIndex = strpos($responseContent, self::ERROR_RESPONSE);


		if ($rootIndex > 0) {

			return $this->parserJSONSource($responseContent, $rootNodeName, $rootIndex);
		} else if ($errorIndex > 0) {

			return $this->parserJSONSource($responseContent, self::ERROR_RESPONSE, $errorIndex);
		} else {

			return null;
		}


	}

	function parserJSONSource($responseContent, $nodeName, $nodeIndex) {
		$signDataStartIndex = $nodeIndex + strlen($nodeName) + 2;
		$signIndex = strpos($responseContent, "\"" . self::SIGN_NODE_NAME . "\"");

        $signDataEndIndex = $signIndex - 1;
		$indexLen = $signDataEndIndex - $signDataStartIndex;
		if ($indexLen < 0) {

			return null;
		}

		return substr($responseContent, $signDataStartIndex, $indexLen);

	}

	function parserJSONSign($responseJSon) {

		return $responseJSon->sign;
	}

	function parserXMLSignData($request, $responseContent) {


		$signData = new \Silksoftwarecorp\Alipay\Model\SignData;

		$signData->sign = $this->parserXMLSign($responseContent);
		$signData->signSourceData = $this->parserXMLSignSource($request, $responseContent);


		return $signData;


	}

	function parserXMLSignSource($request, $responseContent) {


		$apiName = $request->getApiMethodName();
		$rootNodeName = str_replace(".", "_", $apiName) . self::RESPONSE_SUFFIX;


		$rootIndex = strpos($responseContent, $rootNodeName);
		$errorIndex = strpos($responseContent, self::ERROR_RESPONSE);


		if ($rootIndex > 0) {

			return $this->parserXMLSource($responseContent, $rootNodeName, $rootIndex);
		} else if ($errorIndex > 0) {

			return $this->parserXMLSource($responseContent, self::ERROR_RESPONSE, $errorIndex);
		} else {

			return null;
		}


	}

	function parserXMLSource($responseContent, $nodeName, $nodeIndex) {
		$signDataStartIndex = $nodeIndex + strlen($nodeName) + 1;
		$signIndex = strpos($responseContent, "<" . self::SIGN_NODE_NAME . ">");

        $signDataEndIndex = $signIndex - 1;
		$indexLen = $signDataEndIndex - $signDataStartIndex + 1;

		if ($indexLen < 0) {
			return null;
		}


		return substr($responseContent, $signDataStartIndex, $indexLen);


	}

	function parserXMLSign($responseContent) {
		$signNodeName = "<" . self::SIGN_NODE_NAME . ">";
		$signEndNodeName = "</" . self::SIGN_NODE_NAME . ">";

		$indexOfSignNode = strpos($responseContent, $signNodeName);
		$indexOfSignEndNode = strpos($responseContent, $signEndNodeName);


		if ($indexOfSignNode < 0 || $indexOfSignEndNode < 0) {
			return null;
		}

		$nodeIndex = ($indexOfSignNode + strlen($signNodeName));

		$indexLen = $indexOfSignEndNode - $nodeIndex;

		if ($indexLen < 0) {
			return null;
		}

		return substr($responseContent, $nodeIndex, $indexLen);

	}

	/**
	 *
	 * @param $request
	 * @param $signData
	 * @param $resp
	 * @param $respObject
	 * @throws \Exception
	 */
	public function checkResponseSign($request, $signData, $resp, $respObject) {
        $alipayPublicKey = $this->helper->getPaymentConfig('alipay_public_key');
		if (!$this->checkEmpty($alipayPublicKey)) {


			if ($signData == null || $this->checkEmpty($signData->sign) || $this->checkEmpty($signData->signSourceData)) {

				throw new \Exception(" check sign Fail! The reason : signData is Empty");
			}


			$responseSubCode = $this->parserResponseSubCode($request, $resp, $respObject, $this->format);
            if (!$this->checkEmpty($responseSubCode) || ($this->checkEmpty($responseSubCode) && !$this->checkEmpty($signData->sign))) {

				$checkResult = $this->verify($signData->signSourceData, $signData->sign, $this->helper->getPaymentConfig('sign_type'));


				if (!$checkResult) {
          $offset = strpos($signData->signSourceData, "\\/");
					if ($offset !== false && $offset > 0) {

						$signData->signSourceData = str_replace("\\/", "/", $signData->signSourceData);

						$checkResult = $this->verify($signData->signSourceData, $signData->sign, $this->helper->getPaymentConfig('sign_type'));

						if (!$checkResult) {
							throw new \Exception("check sign Fail! [sign=" . $signData->sign . ", signSourceData=" . $signData->signSourceData . "]");
						}

					} else {

						throw new \Exception("check sign Fail! [sign=" . $signData->sign . ", signSourceData=" . $signData->signSourceData . "]");
					}

				}
			}


		}
	}

}
