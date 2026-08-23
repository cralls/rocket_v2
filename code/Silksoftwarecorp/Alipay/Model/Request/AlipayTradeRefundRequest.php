<?php
namespace Silksoftwarecorp\Alipay\Model\Request;
class AlipayTradeRefundRequest
{
	/**
	 * 统一收单交易退款接口
	 **/
	private $bizContent;

	private $apiParas = array();
	private $terminalType;
	private $terminalInfo;
	private $prodCode;
	private $timestamp;
	private $apiVersion="1.0";
	private $notifyUrl;
	private $returnUrl;
    private $needEncrypt=false;

	private $outRequestNo;

	public function solveBizContent($payment, $amount=null){
        $order = $payment->getOrder();
		$creditmemo = $payment->getCreditmemo();
		$comments = $creditmemo->getComments();
		$refundReason = null;
		if(!empty($comments)){
			$refundReason = $comments[0];
		}
        $bizContentArray = array(
            'out_trade_no' => $order->getIncrementId(),
            'trade_no' => $payment->getAdditionalInformation('trade_no'),
			'refund_amount' => $amount?sprintf("%.2f", $amount):sprintf("%.2f", $order->getBaseGrandTotal()),
            'refund_reason' => $refundReason,
			'out_request_no' => /*($amount == null || $amount == $order->getBaseGrandTotal())? null :*/ 'CM'.$creditmemo->getInvoiceId().date('YmdHis'),
        );
        /*$goodsDetail = [];
        foreach($order->getAllVisibleItems() as $item){
            $goodsDetail[] = array(
                'show_url' => $item->getProduct()->getProductUrl()
            );
        }*/
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
		return "alipay.trade.refund";
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
