<?php

namespace Zero1\Pos\Plugin;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\Framework\Event\Observer;
use Zero1\Pos\Helper\Data as PosHelper;

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
     */
    public function beforeValidate($qtyValidator, $observer)
    {
        if($this->posHelper->bypassStock()) {
            $quoteItem = $observer->getEvent()->getItem();

            // todo remove!
            if($quoteItem) {
                $quoteItem->getQuote()->setIsSuperMode(true);
            }
        }
    }
}