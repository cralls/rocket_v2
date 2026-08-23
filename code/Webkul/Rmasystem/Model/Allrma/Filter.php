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
namespace Webkul\Rmasystem\Model\Allrma;

use Magento\Framework\Session\SessionManager;

class Filter
{
    /**
     * @var Session
     */
    protected $session;

    public function __construct(
        SessionManager $session
    ) {
        $this->session = $session;
    }
}
