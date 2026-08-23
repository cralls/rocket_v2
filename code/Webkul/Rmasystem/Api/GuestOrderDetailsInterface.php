<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Api;

interface GuestOrderDetailsInterface
{
    /**
     * Returns selected order detail
     *
     * @api
     * @param int $orderId
     * @return string.
     */
    public function getDetails($orderId);
}
