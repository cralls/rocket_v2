<?php
namespace Silksoftwarecorp\Alipay\Model\Request;
class AlipayTradeWapPayRequest
{
	/**
	 * 手机网站支付接口2.0
	 **/
	 private $bizContent;

 	private $apiParas = array();
 	private $terminalType;
 	private $terminalInfo;
 	private $timestamp;
 	private $prodCode;
 	private $apiVersion="1.0";
 	private $notifyUrl;
 	private $returnUrl;
    private $needEncrypt=false;

     public function solveBizContent($payment, $amount=null){
         $order = $payment->getOrder();
		 if($amount == null){
			 $amount = $order->getBaseGrandTotal();
		 }
         $bizContentArray = array(
             'out_trade_no' => $order->getIncrementId(),
             'product_code' => "FAST_INSTANT_TRADE_PAY",
             'total_amount' => sprintf("%.2f", $amount),
             'subject' => $order->getIncrementId(),
             'body' => $order->getIncrementId(),
             'goods_type' => 1,
             'timeout_express' => "30m"
         );
         $goodsDetail = [];
         foreach($order->getAllVisibleItems() as $item){
             $goodsDetail[] = array(
                 'show_url' => $item->getProduct()->getProductUrl()
             );
         }
         //$bizContentArray['goods_detail'] = json_encode($goodsDetail);
 		$bizContent = json_encode($bizContentArray,JSON_UNESCAPED_UNICODE);
 		$this->setBizContent($bizContent);
 		//$this->setTimestamp($order->getCreatedAt());
     }


	public function setBizContent($bizContent)
	{
		$this->bizContent = $bizContent;
		$this->apiParas["biz_content"] = $bizContent;
	}

	public function getBizContent()
	{
		return $this->bizContent;
	}

	public function getApiMethodName()
	{
		return "alipay.trade.wap.pay";
	}

	public function setNotifyUrl($notifyUrl)
	{
		$this->notifyUrl=$notifyUrl;
	}

	public function getNotifyUrl()
	{
		return $this->notifyUrl;
	}

	public function setReturnUrl($returnUrl)
	{
		$this->returnUrl=$returnUrl;
	}

	public function getReturnUrl()
	{
		return $this->returnUrl;
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getTerminalType()
	{
		return $this->terminalType;
	}

	public function setTerminalType($terminalType)
	{
		$this->terminalType = $terminalType;
	}

	public function getTerminalInfo()
	{
		return $this->terminalInfo;
	}

	public function setTerminalInfo($terminalInfo)
	{
		$this->terminalInfo = $terminalInfo;
	}

	public function getProdCode()
	{
		return $this->prodCode;
	}

	public function setProdCode($prodCode)
	{
		$this->prodCode = $prodCode;
	}

	public function setApiVersion($apiVersion)
	{
		$this->apiVersion=$apiVersion;
	}

	public function getApiVersion()
	{
		return $this->apiVersion;
	}

	public function setTimestamp($timestamp){
		$this->timestamp = $timestamp;
	}

	public function getTimestamp(){
		return $this->timestamp;
	}

  public function setNeedEncrypt($needEncrypt)
  {

     $this->needEncrypt=$needEncrypt;

  }

  public function getNeedEncrypt()
  {
    return $this->needEncrypt;
  }

}
