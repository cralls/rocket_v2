<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'VNS_Events',
    __DIR__
);

require_once(BP.'/lib/internal/dompdf/autoload.inc.php');
