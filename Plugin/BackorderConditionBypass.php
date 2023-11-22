<?php

namespace Zero1\Pos\Plugin;

use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderCondition;
use Zero1\Pos\Helper\Data as PosHelper;

class BackorderConditionBypass
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
     * @param BackOrderCondition $backOrderCondition
     * @param bool $result
     * @param string $sku
     * @param int $stockId
     */
    public function afterExecute($backOrderCondition, $result, $sku, $stockId)
    {
        if($this->posHelper->bypassStock()) {
            $result = true;
        }

        return $result;
    }
}