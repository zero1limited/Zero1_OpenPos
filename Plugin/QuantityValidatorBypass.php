<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\CatalogInventory\Observer\QuantityValidatorObserver;
use Magento\Framework\Event\Observer;

class QuantityValidatorBypass
{
    /**
     * @var OpenPosConfiguration
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
     * @param QuantityValidatorObserver $subject
     * @param callable $proceed
     * @param Observer $observer
     */
    public function aroundExecute(QuantityValidatorObserver $subject, callable $proceed, Observer $observer): void
    {
        if($this->tillSessionManagement->currentlyOnPosStore() && $this->openPosConfiguration->bypassStock()) {
            return;
        }

        $proceed($observer);
    }
}
