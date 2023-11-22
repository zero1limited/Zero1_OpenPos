<?php

namespace Zero1\Pos\Plugin;

use Magento\InventorySales\Model\IsProductSalableCondition\IsAnySourceItemInStockCondition;
use Zero1\Pos\Helper\Data as PosHelper;

class IsProductSalableBypass
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
     * @param IsAnySourceItemInStockCondition $isAnySourceItemInStockCondition
     * @param bool $result
     * @param string $sku
     * @param int $stockId
     */
    public function afterExecute($isAnySourceItemInStockCondition, $result, $sku, $stockId)
    {
        if($this->posHelper->bypassStock()) {
            $result = true;
        }

        return $result;
    }
}