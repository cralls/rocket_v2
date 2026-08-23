<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silksoftware (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Logger\Handler;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Debug extends Base{
    protected $fileName = '/var/log/wechatpay_debug.log';
    protected $loggerType = Logger::DEBUG;
}
