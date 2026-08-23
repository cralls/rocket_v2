<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;
use Silksoftwarecorp\WechatPay\Model\Api\WechatPayException;
use Magento\Framework\App\ObjectManager;
class DataBase
{


	protected $values = array();
	protected $helper;
	protected $logger;
	/**
     *
     * @param \Silksoftwarecorp\WechatPay\Helper\Data  $helper
     * @param \Silksoftwarecorp\WechatPay\Model\Logger $logger
     */
    public function __construct(
    ) {
        $this->helper = ObjectManager::getInstance()->get('Silksoftwarecorp\WechatPay\Helper\Data');
        $this->logger = ObjectManager::getInstance()->get('Silksoftwarecorp\WechatPay\Model\Logger');;
    }


	public function SetSignType($sign_type)
	{
		$this->values['sign_type'] = $sign_type;
		return $sign_type;
	}


	public function SetSign($config)
	{
		$sign = $this->MakeSign($config);
		$this->values['sign'] = $sign;
		return $sign;
	}


	public function GetSign()
	{
		return $this->values['sign'];
	}


	public function IsSignSet()
	{
		return array_key_exists('sign', $this->values);
	}


	public function ToXml()
	{
		if(!is_array($this->values) || count($this->values) <= 0)
		{
    		throw new WechatPayException("Params error");
    	}

    	$xml = "<xml>";
    	foreach ($this->values as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml;
	}


	public function FromXml($xml)
	{
		if(!$xml){
			throw new WechatPayException("xml data error");
		}

        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $this->values;
	}


	public function ToUrlParams()
	{
		$buff = "";
		foreach ($this->values as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}


	public function MakeSign($config, $needSignType = true)
	{
		if($needSignType) {
			$this->SetSignType($config->GetSignType());
		}

		ksort($this->values);

		$string = $this->ToUrlParams();

		$string = $string . "&key=".$config->GetKey();

		if($config->GetSignType() == "MD5"){
			$string = md5($string);
		} else if($config->GetSignType() == "HMAC-SHA256") {
			$string = hash_hmac("sha256",$string ,$config->GetKey());
		} else {
			throw new WechatPayException("Do not support this sign type.");
		}

		$result = strtoupper($string);
		return $result;
	}

	
	public function GetValues()
	{
		return $this->values;
	}
}
