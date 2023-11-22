<?php
namespace Zero1\Pos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\Pos\Helper\Data as PosHelper;

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

    public function execute(Observer $observer)
    {
        // Check if module is enabled
        if(!$this->posHelper->isEnabled()) {
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
