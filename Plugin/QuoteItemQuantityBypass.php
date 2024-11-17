<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\Framework\Event\Observer;

class QuoteItemQuantityBypass
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
     * @param QuantityValidator $qtyValidator
     * @param Observer $observer
     * @return void
     */
    public function beforeValidate(QuantityValidator $qtyValidator, Observer $observer): void
    {
        // TODO: Check if this is still required

        if($this->posHelper->bypassStock()) {
            $quoteItem = $observer->getEvent()->getItem();
            if($quoteItem) {
                $quoteItem->getQuote()->setIsSuperMode(true);
            }
        }
    }
}
