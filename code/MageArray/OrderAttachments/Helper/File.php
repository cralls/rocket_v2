<?php
namespace MageArray\OrderAttachments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class File extends AbstractHelper
{
    const SIZE_1KB = 1024;
    const SIZE_2KB = 2048;
    const SIZE_1MB = 1048576;
    const SIZE_2MB = 2097152;

    const SIZE_BYTES = 'b';
    const SIZE_KBYTES = 'kb';
    const SIZE_MBYTES = 'mb';

    /**
     * @param $fileSize
     * @return string
     */
    public function getTextFileSize($fileSize)
    {
        $result = $fileSize;
        if ($fileSize <= self::SIZE_2KB) {
            $result .= self::SIZE_BYTES;
        } elseif ($fileSize <= self::SIZE_2MB) {
            $result = round($fileSize / self::SIZE_1KB, 2) . self::SIZE_KBYTES;
        } else {
            $result = round($fileSize / self::SIZE_1MB, 2) . self::SIZE_MBYTES;
        }
        return $result;
    }
}
