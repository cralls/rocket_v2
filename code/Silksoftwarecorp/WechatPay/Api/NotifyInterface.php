<?php


namespace Silksoftwarecorp\WechatPay\Api;

interface NotifyInterface
{

    /**
     *
     *
     * @return string Token created
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function notify();



}