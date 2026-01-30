<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;

class ProductIsSalableAfterObserver implements ObserverInterface
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
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Check if module is enabled
        if(!$this->openPosConfiguration->isEnabled()) {
            return;
        }

        // Check we are currently on POS store
        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            return;
        }

        // Check if we should be bypassing stock
        if(!$this->openPosConfiguration->bypassStock()) {
            return;
        }

        // Check product is enabled
        $product = $observer->getProduct();
        if($product->getStatus() != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            return;
        }

        $observer->getSalable()->setData('is_salable', true);
    }
}
