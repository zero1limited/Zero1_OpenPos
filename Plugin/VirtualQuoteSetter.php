<?php

namespace Zero1\Pos\Plugin;

use Magento\Quote\Model\Quote;
use Zero1\Pos\Helper\Data as PosHelper;

class VirtualQuoteSetter
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @param PosHelper $posHelper
     */
    public function __construct(
        PosHelper $posHelper
    ) {
        $this->posHelper = $posHelper;
    }

    /**
     * @param Quote $quote
     * @param bool $result
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsVirtual(Quote $quote, $result)
    {
        if($this->posHelper->currentlyOnPosStore()) {
            return true;
        }

        return $result;
    }
}
