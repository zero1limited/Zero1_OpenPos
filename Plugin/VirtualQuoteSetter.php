<?php

namespace Zero1\Pos\Plugin;

use Magento\Quote\Model\Quote;

class VirtualQuoteSetter
{
    /**
     * @param Quote $quote
     * @param bool $result
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsVirtual(Quote $quote, $result)
    {
        if($quote->getStoreId() == 9) {
            return true;
        }

        return $result;
    }
}
