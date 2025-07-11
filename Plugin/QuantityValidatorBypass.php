<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\CatalogInventory\Observer\QuantityValidatorObserver;
use Magento\Framework\Event\Observer;

class QuantityValidatorBypass
{
    /**
     * @var OpenPosHelper
     */
    protected $posHelper;

    /**
     * @param OpenPosHelper $posHelper
     */
    public function __construct(
        OpenPosHelper $posHelper
    ) {
        $this->posHelper = $posHelper;
    }

    /**
     * @param QuantityValidatorObserver $subject
     * @param callable $proceed
     * @param Observer $observer
     */
    public function aroundExecute(QuantityValidatorObserver $subject, callable $proceed, Observer $observer): void
    {
        if($this->posHelper->currentlyOnPosStore() && $this->posHelper->bypassStock()) {
            return;
        }

        $proceed($observer);
    }
}
