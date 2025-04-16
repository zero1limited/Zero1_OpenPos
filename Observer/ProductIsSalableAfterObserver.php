<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Helper\Data as PosHelper;

class ProductIsSalableAfterObserver implements ObserverInterface
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
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Check if module is enabled
        if(!$this->posHelper->isEnabled()) {
            return;
        }

        // Check we are currently on POS store
        if(!$this->posHelper->currentlyOnPosStore()) {
            return;
        }

        // Check if we should be bypassing stock
        if(!$this->posHelper->bypassStock()) {
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
