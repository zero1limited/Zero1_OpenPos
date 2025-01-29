<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Quote\Model\Quote;

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
     * @return bool
     */
    public function afterIsVirtual(Quote $quote, bool $result): bool
    {
        if($this->posHelper->currentlyOnPosStore() || $quote->getStoreId() === $this->posHelper->getPosStoreId()) {
            return true;
        }

        return $result;
    }
}
