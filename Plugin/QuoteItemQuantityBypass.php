<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\Framework\Event\Observer;

class QuoteItemQuantityBypass
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * @param QuantityValidator $qtyValidator
     * @param Observer $observer
     * @return void
     */
    public function beforeValidate(QuantityValidator $qtyValidator, Observer $observer): void
    {
        // TODO: Check if this is still required

        if($this->openPosConfiguration->bypassStock() && $this->tillSessionManagement->currentlyOnPosStore()) {
            $quoteItem = $observer->getEvent()->getItem();
            if($quoteItem) {
                $quoteItem->getQuote()->setIsSuperMode(true);
            }
        }
    }
}
